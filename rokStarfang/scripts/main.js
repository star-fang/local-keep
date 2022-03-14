import RokMap from "./rokMap.js";

window.onload = function () {
  //localStorage.clear();

  if (indexedDB) {
    //this.alert( 'indexedDB supported');
  } else {
    this.alert('indexedDB not supported');
  }
  start();
} // end window.onload

function start() {

  let defaultMap = null;

  const syncButton = $('#syncButton');
  //const syncLabel = $('#syncLabel');

  syncButton.on('click', function () {
    if (defaultMap == null)
      return false;

    if (defaultMap.isOnLink()) {
      defaultMap.unlink();
      syncButton.css("animation-play-state", "paused");
      //syncLabel.html("sync paused");
    } else {
      defaultMap.link();
      syncButton.css("animation-play-state", "running");
      //syncLabel.html("sync running");
    }
    return false;
  });

  defaultMap = new RokMap(1947);
  defaultMap.loadData().then( function() {
    defaultMap.draw();
    syncButton.click();
  });
} // end start
