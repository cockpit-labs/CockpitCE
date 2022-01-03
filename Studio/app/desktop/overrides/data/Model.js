/**
 * In Ext.data.reader.Reader::extractRecord the call readAssociated reads out the hasMany associations and processes them.
 * This works perfectly for Model.load() since internally a Model is used as record variable in extractRecord. 
 * For Model.save() record extractRecord contains just the Object with the received data from the PUT request, 
 *  therefore readAssociated is never called and no associations are initialized or updated.
 * The following override calls readAssociated if necessary in the save callback.
 */
Ext.override(Ext.data.Model, {
  save: function(options) {
    options = Ext.apply({}, options);
    var me = this,
        includes = me.schema.hasAssociations(me),
        scope  = options.scope || me,
        callback,
        readAssoc = function(record) {
          //basicly this is the same code as in readAssociated to loop through the associations
          var roles = record.associations,
              key, role;
          for (key in roles) {
            if (roles.hasOwnProperty(key)) {
              role = roles[key];
              // The class for the other role may not have loaded yet
              if (role.cls) {
                //update the assoc store too                            
                record[role.getterName]().loadRawData(role.reader.getRoot(record.data));
                delete record.data[role.role];
              }
            }
          }
        };

    //if we have includes, then we can read the associations
    if(includes) {
      //if there is already an success handler, we have to call both
      if(options.success) {
        callback = options.success;
        options.success = function(rec, operation) {
          readAssoc(rec);
          Ext.callback(callback, scope, [rec, operation]);
        };
      }
      else {
        options.success = readAssoc;
      }
    }
    this.callParent([options]);
  },
  /* added getGeneration function in order to make bindings work (See app/bind/Stub override) */
  getGeneration: function() {
    return this.generation;
  }
});