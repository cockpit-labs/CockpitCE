
Ext.define('Studio.ux.field.CronFieldOption', {
  extend: 'Ext.Widget',
  config: {
    text: null,
    value: null
  },
  element: {
    reference: 'element',
    tag: 'option',
  },
  constructor: function(config) {
    // It is important to remember to call the Widget superclass constructor
    // when overriding the constructor in a derived class. This ensures that
    // the element is initialized from the template, and that initConfig() is
    // is called.
    this.callParent([config]);

    // After calling the superclass constructor, the Element is available and
    // can safely be manipulated. Reference Elements are instances of
    // Ext.Element, and are cached on each Widget instance by reference name.
    Ext.getBody().appendChild(this.element);
  },
  updateText: function(text) {
    this.element.append(document.createTextNode(text));
  },
  updateValue: function(text) {
    if (!Ext.isEmpty(text, true)) {
      this.element.dom.setAttribute('value', text);
    }
    else {
      this.element.dom.removeAttribute('value');
    }
  }
});
