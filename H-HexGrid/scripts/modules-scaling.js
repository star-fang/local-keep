const dragValueCriteria = 7;



// hold button module

function HoldButton(btn, action) {
  var interval;
  btn.unbind();
  btn.on('mousedown touchstart', function (e) {
    interval = setInterval(action, 50);
  });


  btn.on('mouseup touchend', function (e) {
    clearInterval(interval);
  });

  btn.on('mouseout', function (e) {
    clearInterval(interval);
  });

};

// end hold button module


function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function setIntervalX(callback, delay, repetitions) {
  var x = 0;
  var intervalID = window.setInterval(function () {
      callback();
      if (++x === repetitions) {
          window.clearInterval(intervalID);
          console.log(callback.name, 'complete');
      }
  }, delay);
}


function Counter(max) {
  this.max = max;
  this.count = 0;

  Counter.prototype.getCount = function () {
      return this.count;
  }

  Counter.prototype.countUp = function () {
      return ++this.count;
  }

  Counter.prototype.getMax = function () {
      return this.max;
  }


  Counter.prototype.isMax = function () {
      return this.count >= this.max;
  }


}




//zoom modules
function ScrollZoom(target, max_scale, factor, scale, selectedIndex, a ) {

  var scale_before = {x:scale.x,y:scale.y};

  if( selectedIndex != undefined) {
    updateScale(target, scale, getHexPos(selectedIndex, a));
  }

  target.parent().on("mousewheel", function(e) {

    e.preventDefault();
    var delta = e.delta || e.originalEvent.wheelDelta;
    if (delta === undefined) {
      delta = e.originalEvent.detail;
    }
    delta = Math.max(-1, Math.min(1, delta)) // cap the delta to [-1,1] for cross browser consistency

    var pos = ( a != undefined )?getHexPos(selectedIndex, a) :
     getMousePos(target[0], e, scale);
   // var pos = getMousePos(target[0], e, scale);
    zoom(target, max_scale, factor, scale, delta, pos, scale_before);
    //if( a != undefined ) {
     //
   //   zoom(target, max_scale, factor, scale, delta);
   // }
  });
}

function zoom(target, max_scale, factor, scale, delta, pos, scale_before) {
  
  //alert(pos.x);

  var size = { w: target.attr('width'), h: target.attr('height') };

  //console.log(size);

  var landscapeMode = window.innerWidth > window.innerHeight; // else : portrait mode

  var min_scale = landscapeMode ? window.innerHeight / size.h : window.innerWidth / size.w;


  if ((scale.x === min_scale && delta < 0)
    || (scale.x === max_scale) && delta > 0) return;

  scale.x += delta * factor * scale.x;
  
  scale.x = Math.max(min_scale, Math.min(max_scale, scale.x));
  scale.y = scale.x;


  
  
  updateScale(target, scale, pos, scale_before);

}

function updateScale(target, scale, pos, scale_before) {
  var left = target.position().left;
  var top = target.position().top;
  var s =  'scale(' + scale.x + ',' + scale.y + ')';
  target.css('transform', s);

  if( scale_before != undefined ) {
  left = left - pos.x * (scale.x - scale_before.x) + 'px';
  top = top - pos.y * (scale.y - scale_before.y)+ 'px';
  target.css({
    left:left,
    top:top});

    scale_before.x = scale.x;
    scale_before.y = scale.y;
  }

}


function DragElement(target, dragValue, scale, selectedIndex, a ) {
  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0, touchDistance = 0;
  const factor = 0.2;
  const parent = target.parent()[0];
  var scale_before = {x:scale.x,y:scale.y};
  

  target.on("mousedown", dragStart);
  target.on("touchstart", dragStart);
  
  function dragStart(e) {
    e = e || window.event;
    //console.log(e.type);
    if (e.type === "touchstart") {
      
      pos3 = e.touches[0].clientX;
      pos4 = e.touches[0].clientY;

      if( e.touches.length == 2 && scale !== undefined) {
        
        touchDistance = Math.pow((e.touches[0].pageX - e.touches[1].pageX),2) + Math.pow((e.touches[0].pageY - e.touches[1].pageY),2);
        } else {
          parent.ontouchend = closeDragElement;
          parent.ontouchmove = elementDrag;
        }
      //console.log("touchstart",pos3,pos4);dddd
     
    } else {
      // get the mouse cursor position at startup:
      pos3 = e.clientX;
      pos4 = e.clientY;
      parent.onmouseup = closeDragElement;
      parent.onmouseleave = closeDragElement;
      parent.onmousemove = elementDrag;
    }


  }

  function elementDrag(e) {
    e = e || window.event;
    if (e.cancelable)
    e.preventDefault();

    dragValue.value++;

    if (e.type === "touchmove") {
      if( e.touches.length == 2 && scale !== undefined) {
        //
        var curTouchDistance = Math.pow((e.touches[0].pageX - e.touches[1].pageX),2) + Math.pow((e.touches[0].pageY - e.touches[1].pageY),2);
        var delta = (curTouchDistance - touchDistance > 0 ? 1 : -1) * factor;
        var pos = getHexPos(selectedIndex, a);

       
        zoom(target, 4.0, factor, scale, delta, pos, scale_before);
        touchDistance = curTouchDistance;
        
        return;
      }


      pos1 = pos3 - e.touches[0].clientX;
      pos2 = pos4 - e.touches[0].clientY;
      pos3 = e.touches[0].clientX;
      pos4 = e.touches[0].clientY;



    } else {
      // calculate the new cursor position:
      pos1 = pos3 - e.clientX;
      pos2 = pos4 - e.clientY;
      pos3 = e.clientX;
      pos4 = e.clientY;
      //console.log("mousemove",pos1,pos2,pos3,pos4);
    }
    // set the element's new position:
    var top = target.offset().top - pos2;
    var left = target.offset().left - pos1;
    var bCheckH = scale == undefined ? 
    ( top +  target.height() > window.innerHeight / 2 && top < window.innerHeight / 2 ) :
    ( top +  target.attr('height')*scale.y > window.innerHeight / 2 && top < window.innerHeight / 2 )
    
    if( bCheckH ) {
    target.css({ top: top + "px" });
    }

    var bCheckW = scale == undefined ? 
      ( left +  target.width() > window.innerWidth / 2 && left < window.innerWidth / 2 ) :
      ( left +  target.attr('width')*scale.x > window.innerWidth / 2 && left < window.innerWidth / 2 )

      if(bCheckW ) {
      target.css({ left: left + "px" });
      }
  
  }

  function closeDragElement() {
    // stop moving when mouse button is released:
    console.log('closeDragElement');
    parent.onmouseup = null;
    parent.onmousemove = null;
    parent.ontouchend = null;
    parent.ontouchmove = null;
    parent.onmouseleave = null;
    dragValue.value = 0;
  }
}

function getMousePos(target, evt, scale) {
  evt = evt || window.event;
  evt.preventDefault();
  var rect = target.getBoundingClientRect();

  if (evt.touches) {
    if( evt.touches.length == 2 ) {
      var touches = evt.changedTouches;
      return {
        x: (touches[0].clientX + touches[1].clientX - 2 * rect.left) / 2 / scale.x,
        y: (touches[0].clientY + touches[1].clientY - 2 * rect.top) / 2 / scale.y
      };
    }
    var touch = evt.changedTouches[0];
    return {
      x: (touch.clientX - rect.left)  / scale.x,
      y: (touch.clientY - rect.top)  / scale.y
    };
  } else {
    return {
      x: (evt.clientX - rect.left) / scale.x,
      y: (evt.clientY - rect.top) / scale.y
    };
  }

}