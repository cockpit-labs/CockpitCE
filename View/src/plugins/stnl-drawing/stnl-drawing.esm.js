/* eslint-disable */
import {template as _template} from 'lodash';
import Konva from 'konva';
import 'regenerator-runtime/runtime';

var colorPickerView = "<div class=\"stnl-drawing-color-picker stnl-drawing-button\">\n    <div class=\"stnl-drawing-picker-background\"></div>\n    <% colors.forEach(function(color) { %>\n        <div class=\"stnl-drawing-color<%= color == selectedColor ? ' selected' : '' %>\"\n         style=\"background-color: <%= color %>\"\n         data-color=\"<%= color %>\"></div>\n    <% }) %>\n</div>\n";

class colorPicker {
  constructor (colors = ['#000000', '#91F325', '#F1F300', '#F300AB', '#00A5FF']) {
    this._colors = colors;
    this._selectedColor = colors[0];
    this._opened = false;
  }
  getView () {
    const template = document.createElement('template');
    const compiled = _template(colorPickerView);
    template.innerHTML = compiled({colors: this._colors, selectedColor: this._selectedColor});
    this._pickerNode = template.content.firstChild;
    this._colorNodes = this._pickerNode.querySelectorAll('.stnl-drawing-color');
    this._colorNodes.forEach((color) => {
      color.onclick = (event) => this.selectColor(event);
    });
    return this._pickerNode
  }
  get color () {
    return this._selectedColor
  }
  set color (color) {
    this._selectedColor = color;
  }
  selectColor (event) {
    if (this._opened) {
      this._colorNodes.forEach((color) => color.classList.remove('selected'));
      this._selectedColor = event.target.dataset.color;
      event.target.classList.add('selected');
      this.close();
      document.dispatchEvent(new window.Event('changecolor'));
    } else {
      this.open();
    }
  }
  open () {
    let y = 0;
    let duration = 0;
    this._colorNodes.forEach((color) => {
      color.style.transitionDuration = `${duration}ms`;
      color.style.transform = `translateY(-${y}px)`;
      y += 48;
      duration += 100;
    });
    this._pickerNode.querySelector('.stnl-drawing-picker-background').style.height = `${y - 3}px`;
    this._pickerNode.querySelector('.stnl-drawing-picker-background').style.transitionDuration = `${duration}ms`;
    this._opened = true;
  }
  close () {
    this._colorNodes.forEach((color) => {
      color.style.transform = `translateY(0)`;
    });
    this._pickerNode.querySelector('.stnl-drawing-picker-background').style.height = `44px`;
    this._opened = false;
  }
}

class Tool {
  constructor () {
    this._colorPicker = new colorPicker();
  }
  get name () {
    return this._name
  }
  get id () {
    return this._id
  }
  get menu () {
    const menuTemplate = document.createElement('div');
    menuTemplate.classList.add('stnl-drawing-menu');
    this._menu.forEach((element) => {
      switch (element.type) {
        case 'colorPicker':
          menuTemplate.appendChild(this._colorPicker.getView());
          break
        case 'separator':
          const separator = document.createElement('span');
          separator.classList.add('stnl-drawing-separator');
          menuTemplate.appendChild(separator);
          break
        default:
          const button = document.createElement('button');
          button.classList.add('stnl-drawing-button');
          button.innerHTML = element.text;
          button.onclick = element.click;
          menuTemplate.appendChild(button);
      }
    });
    return menuTemplate
  }
  get colors () {
    return this._colors
  }
  set colors (colors) {
    this._colors = colors;
  }
}

class Pen extends Tool {
  constructor () {
    super();
    this._name = 'Crayon';
    this._id = 'pen';
    this._history = [];
    this._menu = [
      { type: 'button', text: '<svg class="stnl-drawing-icon"><use xlink:href="#undo"></svg>', click: () => this.undo() },
      { type: 'button', text: '<svg class="stnl-drawing-icon"><use xlink:href="#redo"></svg>', click: () => this.redo() },
      { type: 'separator' },
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#rubbish-bin"></svg>',
        click: () => {
          this.eraseCanvas();
          this._layer.draw();
        }
      },
      { type: 'separator' },
      { type: 'colorPicker' }
    ];
  }
  init (stage) {
    this._stage = stage;
    this._layer = new Konva.Layer({
      id: 'layerPen',
      listening: false
    });
    this._stage.add(this._layer);
    this._canvas = document.createElement('canvas');
    this._canvas.width = this._stage.width();
    this._canvas.height = this._stage.height();
    this._history.push(this._canvas.toDataURL());
    this._image = new Konva.Image({image: this._canvas});
    this._layer.add(this._image);
    this._layer.draw();
    var context = this._canvas.getContext('2d');
    context.lineJoin = 'round';
    context.lineWidth = 5;
    var isPaint = false;
    var lastPointerPosition;
    this._layer.on('mousedown touchstart', () => {
      isPaint = true;
      context.strokeStyle = this._colorPicker.color;
      lastPointerPosition = this._stage.getPointerPosition();
    });
    this._layer.on('mouseup touchend', () => {
      isPaint = false;
      if (this._historyPosition >= 0) {
        this._history = this._history.slice(0, this._historyPosition + 1);
        this._historyPosition = undefined;
      }
      this._history.push(this._canvas.toDataURL());
    });
    this._layer.on('mousemove touchmove', () => {
      if (!isPaint) {
        return
      }
      context.globalCompositeOperation = 'source-over';
      context.beginPath();
      let localPos = {
        x: lastPointerPosition.x - this._image.x(),
        y: lastPointerPosition.y - this._image.y()
      };
      context.moveTo(localPos.x, localPos.y);
      const pos = stage.getPointerPosition();
      localPos = {
        x: pos.x - this._image.x(),
        y: pos.y - this._image.y()
      };
      context.lineTo(localPos.x, localPos.y);
      context.closePath();
      context.stroke();
      lastPointerPosition = pos;
      this._layer.draw();
    });
  }
  enable () {
    this._layer.listening(true);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'block';
  }
  disable () {
    this._layer.listening(false);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'none';
  }
  undo () {
    if (this._history.length < 2) {
      return
    }
    switch (this._historyPosition) {
      case 0:
        return
      case undefined:
        this._historyPosition = this._history.length - 2;
        break
      default:
        this._historyPosition = this._historyPosition - 1;
    }
    console.log('histo', this._history);
    console.log('i', this._historyPosition);
    const previousState = this._history[this._historyPosition];
    this.loadCanvas(previousState);
  }
  redo () {
    if (this._history.length - 1 > this._historyPosition) {
      this._historyPosition = this._historyPosition + 1;
      console.log('redo i', this._historyPosition);
      const nextState = this._history[this._historyPosition];
      this.loadCanvas(nextState);
    }
  }
  loadCanvas (state) {
    const image = new window.Image();
    image.onload = () => {
      this._canvas.getContext('2d').clearRect(0, 0, this._stage.width(), this._stage.height());
      this._canvas.getContext('2d').drawImage(image, 0, 0);
      this._image.setImage(this._canvas);
      this._layer.draw();
    };
    image.src = state;
  }
  eraseCanvas () {
    this._canvas.getContext('2d').clearRect(0, 0, this._stage.width(), this._stage.height());
    this._history.length = 1;
    this._historyPosition = 0;
  }
  getScaledPointerPosition () {
    const pointerPosition = this._stage.getPointerPosition();
    const stageAttrs = this._stage.attrs;
    const x = (pointerPosition.x - stageAttrs.x) / stageAttrs.scaleX;
    const y = (pointerPosition.y - stageAttrs.y) / stageAttrs.scaleY;
    return {x: x, y: y}
  }
  updateCanvasSize () {
    if (this._canvas.width !== Math.trunc(this._stage.width())) {
      this._canvas.width = this._stage.width();
      this._canvas.height = this._stage.height();
      this._canvas.getContext('2d').scale(this._stage.scaleX(), this._stage.scaleY());
      this._canvas.getContext('2d').lineJoin = 'round';
      this._canvas.getContext('2d').lineWidth = 5;
      if (this._history.length > 1) {
        const state = this._history[this._history.length - 1];
        this.loadCanvas(state);
      }
    }
  }
}

class Text extends Tool {
  constructor () {
    super();
    this._name = 'Texte';
    this._id = 'text';
    this._menu1 = [
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#add"></svg><span>Nouveau texte</span>',
        click: () => this.openPrompt()
      },
      { type: 'separator' },
      { type: 'colorPicker' }
    ];
    this._menu2 = [
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#text-2"></svg>',
        click: () => this.editText()
      },
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#rubbish-bin"></svg>',
        click: () => this.removeText()
      },
      { type: 'separator' },
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#text-1"></svg>',
        click: () => this.increaseFontSize()
      },
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#text"></svg>',
        click: () => this.decreaseFontSize()
      },
      { type: 'separator' },
      { type: 'colorPicker' }
    ];
    this._menu = this._menu1;
    this.updateTextColorHandler = this.updateTextColor.bind(this);
  }
  init (stage) {
    this._layer = new Konva.Layer({
      id: 'layerText',
      listening: false
    });
    stage.add(this._layer);
  }
  enable () {
    this._layer.listening(true);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'block';
  }
  disable () {
    if (this.groupSelected) {
      this.groupSelected.fire('unselect');
    }
    this._layer.listening(false);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'none';
  }
  openPrompt () {
    const text = window.prompt('Entrez votre texte');
    if (text) {
      this.addText(text);
    }
  }
  addText (text) {
    if (text) {
      const textNode = new Konva.Text({
        text: text,
        fontSize: 16,
        padding: 5,
        fill: this._colorPicker.color
      });
      const centerPositionX = (this._layer.width() / 2) - (textNode.getWidth() / 2);
      const centerPositionY = (this._layer.height() / 2) - (textNode.getHeight() / 2);
      const group = new Konva.Group({
        x: centerPositionX,
        y: centerPositionY,
        draggable: true
      });
      group.add(textNode);
      this.addAnchors(group);
      group.on('touchstart mousedown', () => {
        this.selectGroup(group);
      });
      group.on('select', () => {
        group.find('Circle').visible(true);
        this._layer.draw();
      });
      group.on('unselect', () => {
        this.groupSelected.getStage().off('tap click');
        this.groupSelected.removeName('selected');
        this.groupSelected = null;
        group.find('Circle').visible(false);
        this._layer.draw();
      });
      this._layer.add(group);
      this.selectGroup(group);
    }
  }
  editText () {
    if (this.groupSelected) {
      const actualText = this.groupSelected.findOne('Text').text();
      const newText = window.prompt('Entrez votre texte', actualText);
      if (newText) {
        this.groupSelected.findOne('Text').text(newText);
        this.updateAnchorsPosition();
        this._layer.draw();
      }
    }
  }
  removeText () {
    if (this.groupSelected) {
      const stage = this.groupSelected.getStage();
      stage.off('tap click');
      this.groupSelected.destroy();
      this.groupSelected = null;
      this._layer.draw();
      this._menu = this._menu1;
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
    }
  }
  addAnchors (group) {
    const textNode = group.find('Text')[0];
    const anchors = this.calculateAnchorsPosition(textNode);
    anchors.forEach(anchorPositions => {
      const anchor = new Konva.Circle({
        x: anchorPositions.x,
        y: anchorPositions.y,
        name: anchorPositions.name,
        stroke: '#666',
        fill: 'rgba(221, 221, 221, 0.9)',
        strokeWidth: 2,
        radius: 8,
        draggable: true,
        dragOnTop: false,
        visible: false
      });
      anchor.on('dragmove', () => {
        this.resize(anchor);
        this._layer.draw();
      });
      anchor.on('mousedown touchstart', () => {
        group.setDraggable(false);
        anchor.moveToTop();
      });
      anchor.on('dragend', () => {
        group.setDraggable(true);
        this._layer.draw();
      });
      anchor.on('mouseover', () => {
        document.body.style.cursor = 'pointer';
        anchor.setStrokeWidth(4);
        this._layer.draw();
      });
      anchor.on('mouseout', () => {
        document.body.style.cursor = 'default';
        anchor.setStrokeWidth(2);
        this._layer.draw();
      });
      group.add(anchor);
    });
  }
  calculateAnchorsPosition (textNode) {
    const positions = [
      {name: 'topLeft', x: textNode.x(), y: textNode.y()},
      {name: 'topRight', x: textNode.x() + textNode.width(), y: textNode.y()},
      {name: 'bottomLeft', x: textNode.x(), y: textNode.y() + textNode.height()},
      {name: 'bottomRight', x: textNode.x() + textNode.width(), y: textNode.y() + textNode.height()}
    ];
    return positions
  }
  updateAnchorsPosition () {
    const textNode = this.groupSelected.findOne('Text');
    textNode.height(undefined);
    const anchors = this.calculateAnchorsPosition(textNode);
    anchors.forEach((anchorPositions) => {
      const actualAnchor = this.groupSelected.findOne(`.${anchorPositions.name}`);
      actualAnchor.x(anchorPositions.x);
      actualAnchor.y(anchorPositions.y);
    });
  }
  resize (activeAnchor) {
    const topLeft = this.groupSelected.findOne('.topLeft');
    const topRight = this.groupSelected.findOne('.topRight');
    const bottomRight = this.groupSelected.findOne('.bottomRight');
    const bottomLeft = this.groupSelected.findOne('.bottomLeft');
    const text = this.groupSelected.findOne('Text');
    const anchorX = activeAnchor.x();
    const anchorY = activeAnchor.y();
    switch (activeAnchor.getName()) {
      case 'topLeft':
        topRight.y(anchorY);
        bottomLeft.x(anchorX);
        break
      case 'topRight':
        topLeft.y(anchorY);
        bottomRight.x(anchorX);
        break
      case 'bottomRight':
        bottomLeft.y(anchorY);
        topRight.x(anchorX);
        break
      case 'bottomLeft':
        bottomRight.y(anchorY);
        topLeft.x(anchorX);
        break
    }
    text.position(topLeft.position());
    const width = topRight.x() - topLeft.x();
    const height = bottomLeft.y() - topLeft.y();
    if (width && height) {
      text.width(width);
      text.height(height);
    }
  }
  selectGroup (group) {
    if (this.groupSelected && this.groupSelected !== group) {
      this.groupSelected.fire('unselect');
    }
    if (this.groupSelected !== group) {
      group.addName('selected');
      group.fire('select');
      this.groupSelected = group;
      this._colorPicker.color = group.findOne('Text').fill();
      this._menu = this._menu2;
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
      document.addEventListener('changecolor', this.updateTextColorHandler);
      const stage = group.getStage();
      stage.on('tap click', (e) => {
        if (e.target.findAncestor('Group') && e.target.findAncestor('Group').hasName('selected')) {
          return
        }
        this.groupSelected.fire('unselect');
        this._menu = this._menu1;
        document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
        document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
        document.removeEventListener('changecolor', this.updateTextColorHandler);
      });
    }
  }
  increaseFontSize () {
    if (this.groupSelected) {
      const actualSize = this.groupSelected.findOne('Text').fontSize();
      this.setFontSize(actualSize + 4);
    }
  }
  decreaseFontSize () {
    if (this.groupSelected) {
      const actualSize = this.groupSelected.findOne('Text').fontSize();
      if (actualSize > 8) {
        this.setFontSize(actualSize - 4);
      }
    }
  }
  setFontSize (size) {
    this.groupSelected.findOne('Text').fontSize(size);
    this.updateAnchorsPosition();
    this._layer.draw();
  }
  updateTextColor () {
    if (this.groupSelected) {
      this.groupSelected.find('Text').fill(this._colorPicker.color);
      this._layer.draw();
    }
  }
}

class Arrow extends Tool {
  constructor () {
    super();
    this._name = 'Flèche';
    this._id = 'arrow';
    this._menu1 = [
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#add"></svg><span>Nouvelle flèche</span>',
        click: () => this.addArrow() },
      { type: 'separator' },
      { type: 'colorPicker' }
    ];
    this._menu2 = [
      { type: 'button',
        text: '<svg class="stnl-drawing-icon"><use xlink:href="#rubbish-bin"></svg>',
        click: () => this.removeArrow() },
      { type: 'separator' },
      { type: 'colorPicker' }
    ];
    this._menu = this._menu1;
    this.updateColorHandler = this.updateColor.bind(this);
  }
  init (stage) {
    this._stage = stage;
    this._layer = new Konva.Layer({
      id: 'layerArraw',
      listening: false
    });
    stage.add(this._layer);
    const rect = new Konva.Rect({
      width: stage.width(),
      height: stage.height(),
      fill: 'transparent'
    });
    this._layer.add(rect);
    this._layer.draw();
  }
  enable () {
    this._layer.listening(true);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'block';
  }
  disable () {
    if (this.groupSelected) {
      this.groupSelected.fire('unselect');
    }
    this._layer.listening(false);
    this._layer.drawHit();
    document.querySelector(`.stnl-drawing-tool-menu[data-tool="${this._id}"]`).style.display = 'none';
  }
  addArrow () {
    var newArrow = new Konva.Arrow({
      x: this._layer.width() / 4,
      y: this._layer.height() / 2,
      points: [0, 0, this._layer.width() / 2, 0],
      pointerLength: 16,
      pointerWidth: 16,
      fill: this._colorPicker.color,
      stroke: this._colorPicker.color,
      strokeWidth: 4
    });
    const group = new Konva.Group({
      draggable: true
    });
    group.add(newArrow);
    this.addAnchors(group);
    group.on('touchstart mousedown', () => {
      this.selectGroup(group);
    });
    group.on('select', () => {
      group.find('Circle').visible(true);
      this._layer.draw();
    });
    group.on('unselect', () => {
      this.groupSelected.getStage().off('tap click');
      this.groupSelected.removeName('selected');
      this.groupSelected = null;
      group.find('Circle').visible(false);
      this._layer.draw();
    });
    this._layer.add(group);
    this.selectGroup(group);
  }
  removeArrow () {
    if (this.groupSelected) {
      const stage = this.groupSelected.getStage();
      stage.off('tap click');
      this.groupSelected.destroy();
      this.groupSelected = null;
      this._layer.draw();
      this._menu = this._menu1;
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
    }
  }
  addAnchors (group) {
    const arrowNode = group.findOne('Arrow');
    const anchors = this.calculateAnchorsPosition(arrowNode);
    anchors.forEach(anchorPositions => {
      const anchor = new Konva.Circle({
        x: anchorPositions.x,
        y: anchorPositions.y,
        name: anchorPositions.name,
        stroke: '#666',
        fill: 'rgba(221, 221, 221, 0.9)',
        strokeWidth: 2,
        radius: 8,
        draggable: true,
        dragOnTop: false,
        visible: false
      });
      anchor.on('dragmove', () => {
        this.resize(anchor);
        this._layer.draw();
      });
      anchor.on('mousedown touchstart', () => {
        group.setDraggable(false);
      });
      anchor.on('dragend', () => {
        group.setDraggable(true);
        this._layer.draw();
      });
      anchor.on('mouseover', () => {
        document.body.style.cursor = 'pointer';
        anchor.setStrokeWidth(4);
        this._layer.draw();
      });
      anchor.on('mouseout', () => {
        document.body.style.cursor = 'default';
        anchor.setStrokeWidth(2);
        this._layer.draw();
      });
      group.add(anchor);
    });
  }
  calculateAnchorsPosition (node) {
    const [x1, y1, x2, y2] = node.getAttr('points');
    const positions = [
        {name: 'bottom', x: node.x() + x1, y: node.y() + y1},
        {name: 'top', x: node.x() + x2, y: node.y() + y2}
    ];
    return positions
  }
  resize (activeAnchor) {
    const arrow = this.groupSelected.findOne('Arrow');
    const top = this.groupSelected.findOne('.top');
    const bottom = this.groupSelected.findOne('.bottom');
    if (activeAnchor.getName() === 'bottom') {
      arrow.x(bottom.x());
      arrow.y(bottom.y());
    }
    const localPos = {
      x: top.x() - arrow.x(),
      y: top.y() - arrow.y()
    };
    arrow.setAttr('points', [0, 0, localPos.x, localPos.y]);
  }
  selectGroup (group) {
    if (this.groupSelected && this.groupSelected !== group) {
      this.groupSelected.fire('unselect');
    }
    if (this.groupSelected !== group) {
      group.addName('selected');
      group.fire('select');
      this.groupSelected = group;
      this._colorPicker.color = group.findOne('Arrow').fill();
      this._menu = this._menu2;
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
      document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
      document.addEventListener('changecolor', this.updateColorHandler);
      const stage = group.getStage();
      stage.on('tap click', (e) => {
        if (e.target.findAncestor('Group') && e.target.findAncestor('Group').hasName('selected')) {
          return
        }
        this.groupSelected.fire('unselect');
        this._menu = this._menu1;
        document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).innerHTML = '';
        document.querySelector(`.stnl-drawing-tool-menu[data-tool='${this.id}']`).appendChild(this.menu);
        document.removeEventListener('changecolor', this.updateColorHandler);
      });
    }
  }
  updateColor () {
    if (this.groupSelected) {
      this.groupSelected.find('Arrow').fill(this._colorPicker.color);
      this.groupSelected.find('Arrow').stroke(this._colorPicker.color);
      this.groupSelected.draw();
    }
  }
}

class Rotate {
  constructor () {
    this._name = 'Rotation';
    this._id = 'rotate';
  }
  init (stage, imgDimension, penTool) {
    this._stage = stage;
    this._penTool = penTool;
    this._image = this._stage.findOne('#image');
    const refWidth = imgDimension.w;
    const refHeight = imgDimension.h;
    const imgRatio = imgDimension.r;
    const container = document.getElementById('stnlDrawingCanvas');
    let stageWidth = refWidth < container.offsetWidth ? refWidth : container.offsetWidth;
    let stageHeight = stageWidth * imgRatio;
    const heightLimit = refHeight < container.offsetHeight ? refHeight : container.offsetHeight;
    if (stageHeight > heightLimit) {
      stageHeight = heightLimit;
      stageWidth = stageHeight / imgRatio;
    }
    this._landscapeSizes = {w: stageWidth, h: stageHeight};
    stageHeight = refHeight < container.offsetHeight ? refHeight : container.offsetHeight;
    stageWidth = stageHeight * imgRatio;
    let widthLimit = refWidth < container.offsetWidth ? refWidth : container.offsetWidth;
    if (stageWidth > widthLimit) {
      stageWidth = widthLimit;
      stageHeight = stageWidth / imgRatio;
    }
    this._portraitSizes = {w: stageWidth, h: stageHeight};
  }
  rotate () {
    switch (this._image.rotation()) {
      case 0:
        this._stage.width(this._portraitSizes.w);
        this._stage.height(this._portraitSizes.h);
        this._image.width(this._portraitSizes.h);
        this._image.height(this._portraitSizes.w);
        this._image.offsetX(this._portraitSizes.h);
        this._image.offsetY(0);
        this.rotateAroundCenter(this._image, -90);
        break
      case -90:
        this._stage.width(this._landscapeSizes.w);
        this._stage.height(this._landscapeSizes.h);
        this._image.width(this._landscapeSizes.w);
        this._image.height(this._landscapeSizes.h);
        this._image.offsetX(this._landscapeSizes.w);
        this._image.offsetY(this._landscapeSizes.h);
        this.rotateAroundCenter(this._image, -180);
        break
      case -180:
        this._stage.width(this._portraitSizes.w);
        this._stage.height(this._portraitSizes.h);
        this._image.width(this._portraitSizes.h);
        this._image.height(this._portraitSizes.w);
        this._image.offsetX(0);
        this._image.offsetY(this._portraitSizes.w);
        this.rotateAroundCenter(this._image, -270);
        break
      default:
        this._stage.width(this._landscapeSizes.w);
        this._stage.height(this._landscapeSizes.h);
        this._image.width(this._landscapeSizes.w);
        this._image.height(this._landscapeSizes.h);
        this._image.offsetX(0);
        this._image.offsetY(0);
        this.rotateAroundCenter(this._image, 0);
        break
    }
    this._image.x(0);
    this._image.y(0);
    this._stage.batchDraw();
    this._penTool.updateCanvasSize();
  }
  rotatePoint ({x, y}, rad) {
    const rcos = Math.cos(rad);
    const rsin = Math.sin(rad);
    return { x: x * rcos - y * rsin, y: y * rcos + x * rsin }
  }
  rotateAroundCenter (node, rotation) {
    const topLeft = { x: -node.width() / 2, y: -node.height() / 2 };
    const current = this.rotatePoint(topLeft, Konva.getAngle(node.rotation()));
    const rotated = this.rotatePoint(topLeft, Konva.getAngle(rotation));
    const dx = rotated.x - current.x;
    const dy = rotated.y - current.y;
    node.rotation(rotation);
    node.x(node.x() + dx);
    node.y(node.y() + dy);
  }
}

var mainView = "<div id=\"stnlDrawing\">\n    <div id=\"stnlDrawingActions\">\n        <button id=\"stnlDrawingActionsCancel\">Annuler</button>\n        <button id=\"stnlDrawingActionsSave\">Enregistrer</button>\n    </div>\n    <div id=\"stnlDrawingMain\">\n        <div id=\"stnlDrawingCanvas\"></div>\n        <div id=\"stnlDrawingToolbars\">\n            <div id=\"stnlDrawingSecondaryToolbar\">\n                <div class=\"stnl-drawing-tool-menu\" data-tool=\"pen\"></div>\n                <div class=\"stnl-drawing-tool-menu\" data-tool=\"text\"></div>\n                <div class=\"stnl-drawing-tool-menu\" data-tool=\"arrow\"></div>\n            </div>\n            <div id=\"stnlDrawingMainToolbar\">\n                <button id=\"stnlDrawingToolsPen\" class=\"stnl-drawing-tool\" data-tool=\"pen\">\n                    <svg class=\"stnl-drawing-icon\"><use xlink:href=\"#pencil\"></svg>\n                </button>\n                <button id=\"stnlDrawingToolsText\" class=\"stnl-drawing-tool\" data-tool=\"text\">\n                    <svg class=\"stnl-drawing-icon\"><use xlink:href=\"#select\"></svg>\n                </button>\n                <button id=\"stnlDrawingToolsArrow\" class=\"stnl-drawing-tool\" data-tool=\"arrow\">\n                    <svg class=\"stnl-drawing-icon\"><use xlink:href=\"#diagonal-arrow\"></svg>\n                </button>\n                <span class=\"stnl-drawing-separator\"></span>\n                <button id=\"stnlDrawingToolsRotate\" class=\"stnl-drawing-tool\" data-tool=\"rotate\">\n                    <svg class=\"stnl-drawing-icon\"><use xlink:href=\"#rotate\"></svg>\n                </button>\n            </div>\n        </div>\n    </div>\n</div>\n";

var svgIconSprite = "<svg xmlns=\"http://www.w3.org/2000/svg\" style=\"display: none;\"><symbol id=\"text-2\" viewBox=\"0 0 512 512\"><title>text-2</title><path d=\"M496,0H16C7.168,0,0,7.168,0,16v96c0,8.832,7.168,16,16,16h176v368c0,8.832,7.168,16,16,16h96c8.832,0,16-7.168,16-16V128 h176c8.832,0,16-7.168,16-16V16C512,7.168,504.832,0,496,0z M480,96H304c-8.832,0-16,7.168-16,16v368h-64V112 c0-8.832-7.168-16-16-16H32V32h448V96z\"/></symbol><symbol id=\"add\" viewBox=\"0 0 42 42\"><title>add</title><polygon points=\"42,19 23,19 23,0 19,0 19,19 0,19 0,23 19,23 19,42 23,42 23,23 42,23 \"/></symbol><symbol id=\"substract\" viewBox=\"0 0 42 42\"><title>substract</title><rect y=\"19\" width=\"42\" height=\"4\"/></symbol><symbol id=\"close\" viewBox=\"0 0 371.23 371.23\"><title>close</title><polygon points=\"371.23,21.213 350.018,0 185.615,164.402 21.213,0 0,21.213 164.402,185.615 0,350.018 21.213,371.23 185.615,206.828 350.018,371.23 371.23,350.018 206.828,185.615 \"/></symbol><symbol id=\"text\" viewBox=\"0 0 512 512\"><title>text</title><g> <g> <path d=\"M256.194,30.718c-3.159-6.88-10.039-11.299-17.617-11.299c-7.578,0-14.458,4.419-17.617,11.299L7.772,495.856L43.006,512 l74.79-163.147h241.581L434.148,512l35.234-16.144L256.194,30.718z M135.549,310.092L238.577,85.314l103.028,224.778H135.549z\"/> </g> </g><g> <g> <path d=\"M476.824,83.201l-25.059,25.059V0h-38.761v108.261l-25.059-25.059l-27.404,27.404l58.142,58.142 c3.779,3.779,8.741,5.679,13.702,5.679c4.961,0,9.923-1.899,13.702-5.679l58.142-58.142L476.824,83.201z\"/> </g> </g></symbol><symbol id=\"text-1\" viewBox=\"0 0 511.992 511.992\"><title>text-1</title><g> <g> <path d=\"M256.19,30.729c-3.159-6.88-10.039-11.299-17.616-11.299s-14.457,4.419-17.616,11.299L7.777,495.849l35.233,16.144 l74.768-163.141H359.35l74.787,163.141l35.233-16.144L256.19,30.729z M135.55,310.092L238.574,85.322l103.024,224.769H135.55z\"/> </g> </g><g> <g> <path d=\"M446.075,5.69c-3.663-3.663-8.992-5.872-13.818-5.678c-5.174,0.019-10.116,2.132-13.74,5.833l-57.481,58.818 l27.733,27.074l24.225-24.806v107.481h38.76V66.156l25.058,25.058l27.403-27.403L446.075,5.69z\"/> </g> </g></symbol><symbol id=\"redo\" viewBox=\"0 0 512 512\"><title>redo</title><path d=\"M503.094,131.359l-156-104c-6.137-4.09-14.027-4.473-20.531-0.992C320.06,29.846,316,36.624,316,44v84H180 C80.748,128,0,208.747,0,308s80.748,180,180,180c11.046,0,20-8.954,20-20c0-11.046-8.954-20-20-20c-77.196,0-140-62.804-140-140 s62.804-140,140-140h136v84c0,7.376,4.06,14.153,10.563,17.633c6.488,3.472,14.378,3.109,20.531-0.992l156-104 c5.564-3.71,8.906-9.954,8.906-16.641S508.658,135.068,503.094,131.359z M356,214.63V81.37L455.944,148L356,214.63z\"/></symbol><symbol id=\"undo\" viewBox=\"0 0 512 512\"><title>undo</title><path d=\"M332,128H196V44c0-7.376-4.06-14.153-10.563-17.633s-14.394-3.099-20.531,0.992l-156,104C3.342,135.068,0,141.313,0,148 s3.342,12.932,8.906,16.641l156,104c6.146,4.097,14.037,4.468,20.531,0.992C191.94,266.153,196,259.376,196,252v-84h136 c77.196,0,140,62.804,140,140s-62.804,140-140,140c-11.046,0-20,8.954-20,20c0,11.046,8.954,20,20,20c99.252,0,180-80.748,180-180 S431.252,128,332,128z M156,214.63L56.056,148L156,81.37V214.63z\"/></symbol><symbol id=\"diagonal-arrow\" viewBox=\"0 0 347.341 347.341\"><title>diagonal-arrow</title><polygon points=\"347.341,99.854 347.34,0 247.487,0.001 247.487,30.001 296.128,30 0,326.128 21.213,347.341 317.34,51.213 317.341,99.854 \"/></symbol><symbol id=\"select\" viewBox=\"0 0 512 512\"><title>select</title><g> <g> <path d=\"M496,128c8.848,0,16-7.168,16-16V16c0-8.832-7.152-16-16-16h-96c-8.848,0-16,7.168-16,16v32H128V16 c0-8.832-7.152-16-16-16H16C7.152,0,0,7.168,0,16v96c0,8.832,7.152,16,16,16h32v256H16c-8.848,0-16,7.168-16,16v96 c0,8.832,7.152,16,16,16h96c8.848,0,16-7.168,16-16v-32h256v32c0,8.832,7.152,16,16,16h96c8.848,0,16-7.168,16-16v-96 c0-8.832-7.152-16-16-16h-32V128H496z M32,96V32h64v64H32z M96,480H32v-64h64V480z M384,400v32H128v-32c0-8.832-7.152-16-16-16H80 V128h32c8.848,0,16-7.168,16-16V80h256v32c0,8.832,7.152,16,16,16h32v256h-32C391.152,384,384,391.168,384,400z M480,416v64h-64 v-64H480z M416,96V32h64v64H416z\"/> </g> </g><g> <g> <path d=\"M270.32,152.832c-5.44-10.832-23.2-10.832-28.64,0.016l-96,192l28.624,14.32L209.888,288h92.224l35.584,71.152 l28.624-14.32L270.32,152.832z M225.888,256L256,195.776L286.112,256H225.888z\"/> </g> </g></symbol><symbol id=\"pencil\" viewBox=\"0 0 297.068 297.068\"><title>pencil</title><path d=\"M288.758,46.999l-38.69-38.69c-5.347-5.354-12.455-8.303-20.02-8.303s-14.672,2.943-20.02,8.297L28.632,190.266L0,297.061 l107.547-28.805L288.745,87.045c5.36-5.354,8.323-12.462,8.323-20.026S294.105,52.347,288.758,46.999z M43.478,193.583 L180.71,55.823l60.554,60.541L103.761,253.866L43.478,193.583z M37.719,206.006l53.368,53.362l-42.404,11.35L26.35,248.384 L37.719,206.006z M279.657,77.951l-19.493,19.505l-60.579-60.541l19.544-19.525c5.823-5.848,16.016-5.842,21.851,0l38.69,38.696 c2.924,2.918,4.544,6.8,4.544,10.926C284.214,71.139,282.594,75.027,279.657,77.951z\"/></symbol><symbol id=\"rubbish-bin\" viewBox=\"0 0 774.266 774.266\"><title>rubbish-bin</title><path d=\"M640.35,91.169H536.971V23.991C536.971,10.469,526.064,0,512.543,0c-1.312,0-2.187,0.438-2.614,0.875 C509.491,0.438,508.616,0,508.179,0H265.212h-1.74h-1.75c-13.521,0-23.99,10.469-23.99,23.991v67.179H133.916 c-29.667,0-52.783,23.116-52.783,52.783v38.387v47.981h45.803v491.6c0,29.668,22.679,52.346,52.346,52.346h415.703 c29.667,0,52.782-22.678,52.782-52.346v-491.6h45.366v-47.981v-38.387C693.133,114.286,670.008,91.169,640.35,91.169z M285.713,47.981h202.84v43.188h-202.84V47.981z M599.349,721.922c0,3.061-1.312,4.363-4.364,4.363H179.282 c-3.052,0-4.364-1.303-4.364-4.363V230.32h424.431V721.922z M644.715,182.339H129.551v-38.387c0-3.053,1.312-4.802,4.364-4.802 H640.35c3.053,0,4.365,1.749,4.365,4.802V182.339z\"/><rect x=\"475.031\" y=\"286.593\" width=\"48.418\" height=\"396.942\"/><rect x=\"363.361\" y=\"286.593\" width=\"48.418\" height=\"396.942\"/><rect x=\"251.69\" y=\"286.593\" width=\"48.418\" height=\"396.942\"/></symbol><symbol id=\"rotate\" viewBox=\"0 0 558.957 558.956\"><title>rotate</title><path d=\"M279.439,441.225c4.989,0,9.67-1.951,13.204-5.47l118.833-118.834c3.523-3.519,5.474-8.223,5.474-13.198 c0-4.975-1.945-9.68-5.474-13.203L292.662,171.7c-7.057-7.042-19.346-7.042-26.4,0l-118.84,118.824 c-3.528,3.519-5.472,8.228-5.472,13.203c0,4.975,1.939,9.675,5.472,13.198l118.817,118.829 C269.771,439.273,274.455,441.225,279.439,441.225z M187.024,303.723l92.434-92.432l92.417,92.432l-92.436,92.427L187.024,303.723 z\"/><path d=\"M483.811,150.913c-40.818-54.577-100.454-90-167.912-99.754c-40.933-5.885-82.598-1.755-121.479,12.106l-27.27-54.507 c-2.705-5.395-8.349-9.311-15.236-8.695c-6.429,0.458-11.887,4.681-13.912,10.781l-35.218,105.7 c-2.751,8.232,1.722,17.212,10.013,20.012l105.7,35.208c5.958,1.951,12.846,0.075,16.928-4.648 c4.233-4.849,5.094-11.724,2.208-17.445l-26.39-52.766c31.89-10.594,65.904-13.619,99.332-8.774 c57.592,8.298,108.49,38.545,143.335,85.165c34.774,46.517,49.354,103.875,41.033,161.537 c-8.326,57.648-38.531,108.557-85.039,143.345c-46.456,34.756-104.071,49.435-161.572,41.132 c-57.587-8.294-108.484-38.536-143.338-85.16c-27.771-37.127-42.764-81.18-43.366-127.384 c-0.128-10.174-8.506-18.426-18.92-18.426c-10.289,0.141-18.552,8.62-18.419,18.921c0.707,54.138,18.276,105.756,50.806,149.281 c40.813,54.567,100.447,90,167.915,99.754c12.295,1.764,24.73,2.66,36.965,2.66c55.134,0,107.798-17.595,152.28-50.871 c54.591-40.828,90.019-100.449,99.745-167.912C541.748,272.724,524.625,205.5,483.811,150.913z\"/></symbol></svg>\n";

class stnlDrawing {
  constructor (src, onSave = () => {}, onCancel = () => {}) {
    this.penTool = new Pen();
    this.textTool = new Text();
    this.arrowTool = new Arrow();
    this.rotateTool = new Rotate();
    this.onSaveCallback = onSave;
    this.onCancelCallback = onCancel;
    this.resizeHandler = this.fitStageIntoParentContainer.bind(this);
    this.createContainer();
    this.createScene(src)
        .then(() => {
          this.initTools();
          this.displayToolbar();
          window.addEventListener('resize', this.resizeHandler);
        });
  }
  createContainer () {
    let template = document.createElement('template');
    const compiled = _template(mainView);
    template.innerHTML = compiled();
    this._mainViewNode = template.content.firstChild;
    template = document.createElement('template');
    template.innerHTML = svgIconSprite;
    this._mainViewNode.insertBefore(
      template.content.firstChild,
      document.getElementById('stnlDrawingActions')
    );
    document.body.appendChild(this._mainViewNode);
    document.getElementById('stnlDrawingActionsCancel')
      .addEventListener('click', this.close.bind(this));
    document.getElementById('stnlDrawingActionsSave')
      .addEventListener('click', this.save.bind(this));
    this.mainToolbar = document.getElementById('stnlDrawingMainToolbar');
    this.secondaryToolbar = document.getElementById('stnlDrawingSecondaryToolbar');
    this._mainViewNode.classList.add('opened');
  }
  async createScene (src) {
    this.imageObject = await this.loadImage(src);
    Konva.pixelRatio = 1;
    this.stage = new Konva.Stage({
      container: '#stnlDrawingCanvas'
    });
    this.imgWidth = this.imageObject.width;
    this.imgHeight = this.imageObject.height;
    this.imgRatio = this.imgHeight / this.imgWidth;
    const container = document.getElementById('stnlDrawingCanvas');
    let stageWidth = this.imgWidth < container.offsetWidth ? this.imgWidth : container.offsetWidth;
    let stageHeight = stageWidth * this.imgRatio;
    const heightLimit = this.imgHeight < container.offsetHeight ? this.imgHeight : container.offsetHeight;
    if (stageHeight > heightLimit) {
      stageHeight = heightLimit;
      stageWidth = stageHeight / this.imgRatio;
    }
    this.refWidth = stageWidth;
    this.refHeight = stageHeight;
    this.stage.width(stageWidth);
    this.stage.height(stageHeight);
    const layer = new Konva.Layer({
      id: 'layerImage',
      listening: true
    });
    const image = new Konva.Image({
      id: 'image',
      image: this.imageObject,
      width: stageWidth,
      height: stageHeight
    });
    layer.add(image);
    this.stage.add(layer);
  }
  initTools () {
    this.penTool.init(this.stage);
    this.arrowTool.init(this.stage);
    this.textTool.init(this.stage);
    this.rotateTool.init(this.stage, {w: this.imgWidth, h: this.imgHeight, r: this.imgRatio}, this.penTool);
  }
  displayToolbar () {
    document.getElementById('stnlDrawingToolsPen').onclick = () => this.selectTool(this.penTool);
    document.getElementById('stnlDrawingToolsText').onclick = () => this.selectTool(this.textTool);
    document.getElementById('stnlDrawingToolsArrow').onclick = () => this.selectTool(this.arrowTool);
    document.getElementById('stnlDrawingToolsRotate').onclick = () => this.rotateTool.rotate();
    this._mainViewNode
      .querySelector(`.stnl-drawing-tool-menu[data-tool='${this.penTool.id}']`)
      .appendChild(this.penTool.menu);
    this._mainViewNode
      .querySelector(`.stnl-drawing-tool-menu[data-tool='${this.textTool.id}']`)
      .appendChild(this.textTool.menu);
    this._mainViewNode
      .querySelector(`.stnl-drawing-tool-menu[data-tool='${this.arrowTool.id}']`)
      .appendChild(this.arrowTool.menu);
    this.selectTool(this.penTool);
  }
  selectTool (tool) {
    if (tool === this.selectedTool) {
      return
    }
    if (this.selectedTool) {
      this.selectedTool.disable();
      this._mainViewNode
        .querySelector(`.stnl-drawing-tool[data-tool="${this.selectedTool._id}"]`)
        .classList.remove('selected');
    }
    this.selectedTool = tool;
    this.selectedTool.enable();
    this._mainViewNode
      .querySelector(`.stnl-drawing-tool[data-tool="${this.selectedTool._id}"]`)
      .classList.add('selected');
  }
  loadImage (src) {
    return new Promise((resolve, reject) => {
      const img = new window.Image();
      img.crossOrigin = 'Anonymous';
      img.onload = () => resolve(img);
      img.onerror = error => reject(Error('Error load image'), error);
      img.src = src;
    })
  }
  fitStageIntoParentContainer () {
    const container = document.getElementById('stnlDrawingCanvas');
    let stageWidth = container.offsetWidth;
    let stageHeight = stageWidth * this.imgRatio;
    if (stageHeight > container.offsetHeight) {
      stageHeight = container.offsetHeight;
      stageWidth = stageHeight / this.imgRatio;
    }
    const widthRatio = stageWidth / this.refWidth;
    const heightRatio = stageHeight / this.refHeight;
    this.stage.width(stageWidth);
    this.stage.height(stageHeight);
    this.stage.scale({x: widthRatio, y: heightRatio});
    this.stage.batchDraw();
  }
  getImage () {
    const wr = this.imageObject.width / this.stage.width();
    const hr = this.imageObject.height / this.stage.height();
    this.stage.width(this.imageObject.width);
    this.stage.height(this.imageObject.height);
    this.stage.scale({x: wr, y: hr});
    this.stage.batchDraw();
    return this.stage.toDataURL({
      mimeType: 'image/jpeg',
      quality: 1,
      pixelRatio: 1
    })
  }
  save () {
    this.selectedTool.disable();
    this.onSaveCallback(this.getImage());
    this.destroy();
  }
  close () {
    this.onCancelCallback();
    this.destroy();
  }
  destroy () {
    window.removeEventListener('resize', this.resizeHandler);
    this._mainViewNode.remove();
  }
}

export default stnlDrawing;
/* eslint-enable */
