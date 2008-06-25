/*********************************************************************************

  Script    : mBox
  Version   : 1.2.5
  Updated   : 03/28/2008
  Author    : Marcos Esperón <Hanok>, <hanokmail[at]gmail.com>
  Web       : <http://www.hnkweb.com>
  Desc      : Generate a slideshow based on JSon collection.
  Licence   : Open Source MIT Licence.

**********************************************************************************

  Based on Mootools v1.11
	  Copyright (c) 2007 Valerio Proietti, <http://mad4milk.net>

    The MIT License

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
      

*********************************************************************************/
 
var mBox = new Class({
	
  options: {
    mode: 'w',
    showthumbs: true,
    download: true,
    zoom: false,
    width: 600,
    height: 450,		
    help: '',
    delay: 500,
    transition: Fx.Transitions.quintOut,
    timer: 5000,
    autostart: false
  },

  initialize: function(options) {

    this.setOptions(options);		

    this.options.urlbase = this.options.siteurl+'/wp-content/plugins/mbox';				
    this.box = $('mbox-'+this.options.id);

    this.options.width = this.options.width.toInt();
    this.options.height = this.options.height.toInt();
    if (this.options.autostart == "true") {
      this.options.autostart = true;
    };
    if (this.options.download == "false") {
      this.options.download = false;
    };
    if (this.options.zoom == "true") {
      this.options.zoom = true;
    };

    this.box.setStyles({'width': this.options.width+'px', 'height': this.options.height+24+'px'});
    
    this.options.perpage = Math.floor((this.options.width-50) / 25);

    this.loading();
    this.generate();

    if (this.options.help != '') {
      var help = new Element('p', {'id': 'mbox-help-'+this.options.id, 'class': 'mbox-help'}).setHTML(this.options.help).injectAfter(this.box);
    };    

  },

  loading: function() {
  
    this.box.empty();
    var load = new Element('div', {'class': 'mbox-load'});
    load.inject(this.box);
    
  },
	
  generate: function() {
  
    switch (this.options.mode) {
      case  "f":
        var url = this.options.urlbase+'/flickr.php?t='+this.options.flickr_tags;
        break;
      case  "d":
        var url = this.options.urlbase+'/fldr.php?p='+this.options.folder;
        break;
      default:   
        var url = this.options.urlbase+'/db.php?id='+this.options.post;
        break;
    };

    var request = new Json.Remote(url, {
      onComplete: function(jsonObj) {
        this.colection = jsonObj.previews;
        this.process();
      }.bind(this)
    }).send();
    
  },
	
  process: function(page) {      
  
    if (typeof page == "undefined") { page = 1; };
    var min = (page-1) * this.options.perpage;
    var max = (page * this.options.perpage)-1;	

    this.box.empty();    
    this.images	=  new Array();
    this.photoid = -1;
    i = 0; p = 0;    

    if (this.colection.length > 0) {      

      this.colection.each(function(img) {	          
        if (p >= min && p <= max) {

          if (this.options.mode == "w" || this.options.mode == "f") {
            src = img.src;
            thumb = img.thumb;
          } else {
            src = '/'+this.options.folder+'/'+img.src;
            thumb = '/'+this.options.folder+'/'+img.thumb;
          };
          if (img.thumb == "") {
            thumb = this.options.siteurl+'/wp-content/plugins/mbox/img/noimage.gif';
          }

          this.images[i] = {
            'id': img.id,
            'value': p+1,
            'src': src,
            'thumb': thumb,
            'title': img.title,
            'desc': img.desc,
            'file': img.file
          };
          new Asset.image(this.images[i].thumb); 
          i++;       
        };
        p++;
      }.bind(this));
      
      this.navigationbar(page);

      this.box.links = $$('#mbox-'+this.options.id + ' a.a-box');

      this.removeevents();    

      var fcon = new Element('div', {'id': 'mbox-fcon-'+this.options.id, 'class': 'mbox-fcon'}).injectTop(this.box);         
      var foto = new Element('img', {'id': 'mbox-foto-'+this.options.id, 'class': 'mbox-foto'}).injectInside(fcon);

      var info = new Element('div', {'id': 'mbox-info-'+this.options.id, 'class': 'mbox-info'}).injectTop(this.box);
      var capt = new Element('p', {'id': 'mbox-capt-'+this.options.id, 'class': 'mbox-capt'}).setHTML('').injectInside(info);
      var desc = new Element('p', {'id': 'mbox-desc-'+this.options.id, 'class': 'mbox-desc'}).setHTML('').injectInside(info);

      if (this.options.download) {
        var down = new Element('a', {'id': 'mbox-down-'+this.options.id, 'class': 'mbox-down'}).injectInside(info);
      };
      if (this.options.zoom) {
        var zoom = new Element('a', {'id': 'mbox-zoom-'+this.options.id, 'class': 'mbox-zoom'}).injectInside(info);
      };                
       
      this.setevents();      
      if (this.options.autostart) {
        this.toggleshow();
      } else {
        this.nextImg();
      };
  
    } else {
      var alrt = new Element('p', {'id': 'mbox-alrt-'+this.options.id, 'class': 'mbox-alrt'}).setHTML(this.options.alert).injectInside(this.box);
      this.box.setStyles({'height': '50px'});
    };

  },
	
  navigationbar: function(page) {

    var navi = new Element('div', {'id': 'mbox-navi-'+this.options.id, 'class': 'mbox-navi'}).injectTop(this.box);

    var thli = new Element('ul', {'id': 'mbox-thli-'+this.options.id, 'class': 'mbox-thli'}).inject(navi);

    this.images.each(function(img, key){
      var item = new Element('li', {'class': 'item'}).inject(thli);
      var link = new Element('a', {'id': img.id, 'href': img.src, 'class': 'a-box', 'title' : img.title, 'target': '_blank'});               
      var numb = new Element('span').setHTML(img.value).injectInside(link);
      if (this.options.showthumbs) {    
        var imag = new Element('img', {'src': img.thumb, 'alt': img.title, 'title': 'Click para ampliar', width: 50, height: 50}).injectInside(link);
      };
      link.injectInside(item);
    }.bind(this));
    
    var slid = new Element('a', {'id': 'mbox-slid-'+this.options.id, 'class': 'mbox-slid', 'href': 'javascript:void(0);'}).setHTML('activar').injectInside(navi);
    $('mbox-slid-'+this.options.id).addEvent( 'click', function(){ this.toggleshow(); }.bind(this)); 

    this.paginator(page, navi);
    
  },
	
  paginator: function(page, navi) {
	
		if (this.colection.length > this.options.perpage && this.options.perpage > -1) { 
      var pags = new Element('div', {'id': 'mbox-pags-'+this.options.id, 'class': 'mbox-pags'}).inject(navi);     
      var totalpags = Math.ceil(this.colection.length / this.options.perpage);
      if (page-1 > 0) {
        var prev = new Element('a', {'id': 'mbox-prev-'+this.options.id, 'class': 'mbox-prev', 'href': page-1, 'title': page-1}).inject(pags); 
        $('mbox-prev-'+this.options.id).addEvent( 'click', function(e){ e = new Event(e).stop(); this.process(page-1); }.bind(this));      
      };
      if (page+1 <= totalpags) {
        var next = new Element('a', {'id': 'mbox-next-'+this.options.id, 'class': 'mbox-next', 'href': page+1, 'title': page+1}).inject(pags);
        $('mbox-next-'+this.options.id).addEvent( 'click', function(e){ e = new Event(e).stop(); this.process(page+1); }.bind(this));      
      };
    };
    
  },
	
  setevents: function() {

    $each(this.box.links, function(el, idx) {
      el.addEvent('click', function(e) {
        new Event(e).stop();
        this.swaptoid(idx);        
      }.bind(this));
    }.bind(this)); 

    if (this.options.download) {
      $('mbox-down-'+this.options.id).addEvent("click", function(e) {
        window.open(this.href);
        new Event(e).stop();
      });
    };
    
    if (this.options.zoom) {
      $('mbox-zoom-'+this.options.id).addEvent("click", function(e) {        
        this.stopslide();
        Lightbox.show($('mbox-zoom-'+this.options.id).href, '');
        new Event(e).stop();
      }.bind(this));
    };

    document.addEvent('keydown', function(event){
      event = new Event(event);
      if (event.key == 'right') {
        this.nextImg();
      } else if (event.key == 'left') {
        this.prevImg();
      };
    }.bind(this));

  },
	
  removeevents: function() {

    $each(this.box.links, function(el, idx) {
      el.removeEvents();
    }.bind(this)); 

    document.removeEvents();

  },
	
  swaptoid: function(id) {

    this.photoid = id;
    this.activate();

    this.startmotion();

    new Asset.images(this.current.src, { 
      onComplete: function() {
        tmp = new Image();
        tmp.src = this.current.src;
        if (tmp.width > this.options.width || tmp.height > this.options.height) { 
          if (tmp.width > tmp.height) {
            this.current.height = (this.options.width*tmp.height) / tmp.width;             
            this.current.width = this.options.width;            
          } else {
            this.current.width = (this.options.height*tmp.width) / tmp.height; 
            this.current.height = this.options.height;        
          };
          if (this.current.height > this.options.height) { this.current.height = this.options.height };
          if (this.current.width > this.options.width) { this.current.width = this.options.width };
        } else {
          this.current.height = tmp.height;
          this.current.width = tmp.width;
        };
        this.endmotion();
        delete tmp;
      }.bind(this)
    });

  },
	
  startmotion: function(){
  	
    $('mbox-foto-'+this.options.id).src = null;
    wCur = $('mbox-fcon-'+this.options.id).getStyle('width').toInt();
    hCur = $('mbox-fcon-'+this.options.id).getStyle('height').toInt();
    //$('mbox-fcon-'+this.options.id).setStyles({'background-image': 'url('+this.options.loadimg+')'});
    $('mbox-foto-'+this.options.id).setStyles({opacity: 0, display: 'none'});
    $('mbox-capt-'+this.options.id).setStyles({opacity: 0});
    $('mbox-desc-'+this.options.id).setStyles({opacity: 0});
    
  },
	
  endmotion: function(){	
    
    var myPhoto = new Fx.Styles('mbox-fcon-'+this.options.id, { duration: this.options.delay, transition: this.options.transition }).custom({
      'height': [hCur, this.current.height],
      'width': [wCur, this.current.width]
    });       
    $('mbox-foto-'+this.options.id).src = this.current.src;
    $('mbox-foto-'+this.options.id).height = this.current.height;
    $('mbox-foto-'+this.options.id).width = this.current.width; 

    if (this.current.height < this.options.height) {
      dif = this.options.height - this.current.height;
      $('mbox-foto-'+this.options.id).setStyles({'margin-top': dif/2});
    } else {
      $('mbox-foto-'+this.options.id).setStyles({'margin-top': 0});
    };

    if (this.options.download) { $('mbox-down-'+this.options.id).href = this.current.src; };
    if (this.options.zoom) { $('mbox-zoom-'+this.options.id).href = this.current.src; };
    $('mbox-capt-'+this.options.id).setHTML('<strong>'+(this.current.value)+'/'+this.colection.length+'</strong>&nbsp;|&nbsp;'+this.current.title);

    if (this.options.mode == "f") {
      $('mbox-desc-'+this.options.id).setHTML("<a href=\""+this.current.desc+"\">"+this.current.desc+"</a>");
    } else {
      $('mbox-desc-'+this.options.id).setHTML(this.current.desc);
    };

    this.showimage.bind(this).delay(this.options.delay);

  },
	
  showimage: function() {
  
    $('mbox-foto-'+this.options.id).setStyles({display: 'block'});	  
    $('mbox-foto-'+this.options.id).effect('opacity').custom(0,1).chain(function(){
    $('mbox-capt-'+this.options.id).effect('opacity').custom(0,1).chain(function(){
    $('mbox-desc-'+this.options.id).effect('opacity').custom(0,1);
    //$('mbox-fcon-'+this.options.id).setStyles({'background-image': 'none'});
    }.bind(this));
    }.bind(this));
    
  },
	
  toggleshow: function(){
  
    if(this.slideshow){
      this.stopslide();      
    } else {
      this.startslide();      
    };
  
  },
	
  startslide: function(){
  
    $('mbox-capt-'+this.options.id).setHTML('Iniciando la presentacion...');
    this.nextImg();
    this.slideshow = (function(){ this.nextImg(); }).bind(this).periodical(this.options.timer);
    $('mbox-slid-'+this.options.id).setHTML('Parar');
    $('mbox-slid-'+this.options.id).setStyles({'background-position': '0px -16px'});
  
  },

  stopslide: function(){
  
    $clear(this.slideshow);
    this.slideshow = null;
    $('mbox-slid-'+this.options.id).setHTML('Activar');
    $('mbox-slid-'+this.options.id).setStyles({'background-position': '0px 0px'});
  
  },
	
  nextImg: function(){
  
    if (this.photoid == (this.images.length - 1)) {
      this.photoid = 0;
    } else {
      this.photoid++;
    };
    this.swaptoid(this.photoid);
  
  },
	
  prevImg: function(){
    
    if (this.photoid == 0) {
      this.photoid = this.images.length - 1;
    } else {
      this.photoid--;
    };
    this.swaptoid(this.photoid);
    
  },

  activate: function() {

    this.current = this.images[this.photoid];
    $each(this.box.links, function(el) {
      if (this.current.id == el.id) {
        el.addClass('active');
      } else {
        el.removeClass('active');
      };
    }.bind(this));
    
  }

});

mBox.implement(new Chain, new Options, new Events);
