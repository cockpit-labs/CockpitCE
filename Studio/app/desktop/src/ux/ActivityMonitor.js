/**
 * @class Studio.ux.ActivityMonitor
 * @version 1.1
 * @example
 *   Studio.ux.ActivityMonitor.init({ verbose : true });
 *   Studio.ux.ActivityMonitor.start();
 **/
Ext.define('Studio.ux.ActivityMonitor', {
  requires: [
    'Ext.Dialog',
    'Ext.TaskManager',
    'Ext.util.TaskRunner',
    'Ext.Component'
  ],
  singleton: true,
  ui: null,
  runner: null,
  task: null,
  lastActive: null,
  ready: false,
  /** (Boolean): Whether or not the ActivityMonitor() should output messages to the JavaScript console. */
  verbose: false,
  /** interval (Integer): How often (in milliseconds) the monitorUI() method is executed after calling start() */
  interval: (1000 * 30 * 1), // 30 seconds
  /** maxInactive (Integer): The longest amount of time to consider the user "active" without registering new mouse movement or keystrokes */
  maxInactive: (1000 * 60 * 5), // 5 minutes
  /**
   * isActive (Function): Called each time monitorUI() detects the user is currently active (defaults to Ext.emptyFn)
   * the monitor is passed (this) as first argument
   */
  isActive: Ext.emptyFn,
  /**
   * isInactive (Funtion): Called when monitorUI() detects the user is inactive (defaults to Ext.emptyFn)
   * the monitor is passed (this) as first argument
   */
  isInactive: Ext.emptyFn,
  /**
   * additional function to be called at each interval
   */
  fn: Ext.emptyFn,
  scope: null,
  /**
   * isExpiring is used to suspend monitoring while waiting for user to choose to keep session alive or not
   */
  isExpiring: false,

  init: function(config) {
    if (!config) { config = {}; }
    Ext.apply(this, config, {
      runner: new Ext.util.TaskRunner(),
      ui: Ext.getBody(),
      task: {
        run: this.monitorUI,
        interval: config.interval || this.interval,
        scope: this
      }
    });
    this.ready = true;
  },

  isReady: function() {
    return this.ready;
  },

  start: function() {
    if (!this.isReady()) {
      this.log('Please run ActivityMonitor.init()');
      return false;
    }

    this.ui.on('singletap', this.captureActivity, this);

    this.lastActive = new Date();
    this.log('ActivityMonitor has been started.');
    this.runner.start(this.task);
  },

  stop: function() {
    if (!this.isReady()) {
      this.log('Please run ActivityMonitor.init()');
      return false;
    }

    this.runner.stop(this.task);
    this.lastActive = null;
    this.ui.un('singletap', this.captureActivity, this);

    this.log('ActivityMonitor has been stopped.');
  },

  captureActivity: function(eventObj, el, eventOptions) {
    this.lastActive = new Date();
//    console.log("captureActivity");
  },

  monitorUI: function() {
//    console.log("monitorUI");
    var now = new Date();
    var inactive = (now - this.lastActive);
    if (inactive >= this.maxInactive) {
      if (this.isExpiring === true) {
        return;
      }
      this.isExpiring = true;
      // refresh token in case of expiring during messagebox
      if (this.fn) {
        this.fn.call(this.scope);
      }
      var wait = 15;
      var task = {
        interval: 1000,
        scope: this
      };
      var dialog = Ext.create({
        xtype: 'dialog',
        title: 'Warning',
        buttons: {
          yes: {
            handler: function () {
              Ext.TaskManager.stop(task);
              this.isExpiring = false;
              this.captureActivity();
              dialog.destroy();
            },
            scope: this
          },
          no: {
            handler: function () {
              Ext.TaskManager.stop(task);
              this.stopMonitoring();
              dialog.destroy();
            },
            scope: this
          }
        },
        items: {
          html: 'Your session is expiring due to inactivity. Do you want to stay connected ?'
        },
        listeners: {
          scope: this,
          show: function(cmp) {
            task.run = function() {
              // refresh token in case of expiring during messagebox
              if (this.fn) {
                this.fn.call(this.scope);
              }
              if( wait < 1 ){
                Ext.TaskManager.stop(task);
                dialog.destroy();
                this.stopMonitoring();
              } else {
                cmp.down('button[itemId="yes"]').setText('yes (' + wait + ')');
              }
              wait--;
            };
            Ext.TaskManager.start(task);
          }
        }
      });
      dialog.show();
    }
    else {
        this.log('CURRENTLY INACTIVE FOR ' + inactive + ' (ms)');
        this.isActive(this);
        if (this.fn) {
          this.fn.call(this.scope);
        }
    }
  },
  stopMonitoring: function() {
    this.log('MAXIMUM INACTIVE TIME HAS BEEN REACHED');
    this.stop(); //remove event listeners
    this.isInactive(this);
  },
  log: function(msg) {
    if (this.verbose) {
      console.log(msg);
    }
  }
});