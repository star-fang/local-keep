function getMousePos(target, evt, zoomValue, fixedX, fixedY) {
    evt = evt || window.event;
    evt.preventDefault();
    var rect = target.getBoundingClientRect();
  
    var fixFactorX = (typeof fixedX !== "undefined" ? (fixedX / target.width) : 1.0);
    var fixFactorY = (typeof fixedY !== "undefined" ? (fixedY / target.height) : 1.0);
  
    if (evt.touches) {
      var touch = evt.changedTouches[0];
      return {
        x: (touch.clientX - rect.left) * fixFactorX / zoomValue,
        y: (touch.clientY - rect.top) * fixFactorY / zoomValue
      };
    } else {
      return {
        x: (evt.clientX - rect.left) * fixFactorX / zoomValue,
        y: (evt.clientY - rect.top) * fixFactorY / zoomValue
      };
    }
  
  }