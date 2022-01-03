
Ext.define('Studio.ux.field.CronField', {
  extend: 'Ext.field.Field',
    xtype: 'croninputfield',

    isSelectField: true,
    /**
     * @property {String} tag
     * The tag name to use for this field's input element. Subclasses should override this
     * property on their class body.  Not intended for instance-level use.
     * @protected
     */
    tag: 'select',
    cls: 'cron-field',
    /**
     * @property defaultBindProperty
     * @inheritdoc
     */
     defaultBindProperty: 'value',

     /**
      * @cfg twoWayBindable
      * @inheritdoc
      */
     twoWayBindable: {
         value: 1
     },
 
     /**
      * @cfg publishes
      * @inheritdoc
      */
     publishes: {
         value: 1
     },
     config: {
        inputSize: 8,
        width: 150,
        options: [],
        multiple: false,
        /**
         * @cfg {Boolean} [readOnly=false]
         * `true` to set the field DOM element `readonly` attribute to `"true"`.
         *
         * Mutation of {@link Ext.field.Text text fields} through triggers is also disabled.
         *
         * To simply prevent typing into the field while still allowing mutation through
         * triggers, set {@link Ext.field.Text#cfg!editable} to `false`.
         * @accessor
         */
        readOnly: false,

        /**
         * @private
         */
        inputValue: null
    },

    focusEl: 'inputElement',
    ariaEl: 'inputElement',
    inputTabIndex: 0,

    getBodyTemplate: function() {
        return [this.getInputTemplate()];
    },

    getInputTemplate: function() {
      var template = {
        tag: this.tag,
        reference: 'inputElement',
        tabindex: this.inputTabIndex,
        cls: 'cron-field-el'
      };
      template.listeners = template.listeners || {};
      template.listeners.change = {
          fn: 'onChange',
          delegated: false
      };
      return template;
    },
    onChange: function(e) {
      var me = this;
      me.$onChange = true;
      var options = me.inputElement.dom.selectedOptions;
      if (options.length == 0) {
        // force at least one value
        me.inputElement.dom.value = '*';
      } else {
        for (var i = 0; i < options.length; i++) {
          if (options[i].value == '*') {
            me.inputElement.dom.value = '*';
          }
        }  
      }
      me.setValue(Ext.Array.pluck(me.inputElement.dom.selectedOptions, 'value'));
      delete me.$onChange;
    },
    // onChange: function(me, value, startValue) {
    //   me.fireEvent('change', this, value, startValue);
    // },
    updateOptions: function(options) {
      if (!options) {
        return;
      }
      var me = this;
      for (var i=0; i < options.length; i++) {
        var option = me.factoryOptions(options[i]);
        me.inputElement.dom.add(option.element.dom);
      }
    },
    factoryOptions: function(option) {
      var thumb = Ext.create('Studio.ux.field.CronFieldOption', option);
      thumb.doInheritUi();
      return thumb;
    },
    initElement: function() {
        this.callParent();
        this.labelElement.dom.setAttribute('for', this.inputElement.id);
    },
    updateDisabled: function(disabled, oldDisabled) {
        this.callParent([disabled, oldDisabled]);
        this.inputElement.dom.disabled = !!disabled;
    },
    updateInputSize: function(newInputSize) {
        this.setInputAttribute('size', newInputSize);
    },
    updateMultiple: function(newMultipleValue) {
      this.setInputAttribute('multiple', newMultipleValue ? newMultipleValue: null);
    },
    updateName: function(name, oldName) {
        this.callParent([name, oldName]);
        this.setInputAttribute('name', name);
    },
    updateReadOnly: function(readOnly) {
        this.setInputAttribute('readonly', readOnly ? true : null);
    },
    updateValue: function(value, oldValue) {
      var me = this;
      if (Ext.isArray(value)) {
        var forceFalse = false;// value.indexOf('*') != -1;
        if (!me.$onChange || forceFalse) {
          console.log('setting field options');
          var options = this.inputElement.dom.options;
          for (var i = 0; i < options.length; i++) {
            var optionValue = options[i].value;
            if (forceFalse && optionValue != '*') {
              options[i].selected = false;
            } else {
              options[i].selected = (value.indexOf(optionValue) != -1);
            }
          }
        }
      }
      this.callParent([value, oldValue]);
    },
    getRawValue: function() {
      return this.getValue();
    },
    privates: {
        /**
         * Helper method to update or remove an attribute on the `inputElement`
         * @private
         */
        setInputAttribute: function(attribute, newValue) {
            var inputElement = this.inputElement.dom;

            if (!Ext.isEmpty(newValue, true)) {
                inputElement.setAttribute(attribute, newValue);
            }
            else {
                inputElement.removeAttribute(attribute);
            }
        }
    }
});
// /**
//  * Cron field container used for Calendars
//  */
// Ext.define('Studio.ux.field.CronField', {
//   extend: 'Ext.field.Container',
//   requires: [
//     'Ext.field.Select',
//     'Ext.field.RadioGroup',
//     'Studio.ux.field.CronPartField'
//   ],
//   xtype: 'cronfield',
//   cls: 'cron-field',
//   defaults: {
//     labelTextAlign: 'center'
//   },
//   initConfig: function (instanceConfig) {
//     var conf = instanceConfig || {};
//     Ext.apply(instanceConfig, {
//       items: [
//         {
//           xtype: 'cronpartfield',
//           partType: 'day',
//           name: conf.name + 'day'
//         },
//         {
//           xtype: 'cronpartfield',
//           partType: 'month',
//           name: conf.name + 'month'
//         },
//         {
//           xtype: 'cronpartfield',
//           partType: 'week',
//           name: conf.name + 'week'
//         }
//       ]
//     });
//     this.callParent(arguments);
//   },
//   getTemplate: function() {
//     var tpl = this.callParent();
//     var labelEl = tpl[0];
//     labelEl.children.push({
//       reference: 'labelTextHelper',
//       cls: Ext.baseCSSPrefix + 'label-text-el',
//       tag: 'span'
//     });
//     return tpl;
//   }
// });
