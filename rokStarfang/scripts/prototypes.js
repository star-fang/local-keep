Image.prototype.load = function (url, progress_callback, onload_callback) {

    //console.log(url);
    var thisImg = this;
    var xmlHTTP = new XMLHttpRequest();
    xmlHTTP.open('GET', url, true);
    xmlHTTP.responseType = 'arraybuffer';
    xmlHTTP.onload = function (e) {
        //console.log('dd',e);
        //console.log(this);
        var blob = new Blob([this.response]);
        thisImg.src = window.URL.createObjectURL(blob);
        if (onload_callback != undefined) {
            onload_callback(blob);
        }
    };
    xmlHTTP.onprogress = function (e) {
        thisImg.completedPercentage = parseInt((e.loaded / e.total) * 100);
        if (progress_callback != undefined && progress_callback) {
            progress_callback(thisImg.completedPercentage);
        }
    };
    xmlHTTP.onloadstart = function () {
        thisImg.completedPercentage = 0;
        if (progress_callback != undefined && progress_callback) {
            progress_callback(0);
        }
    };
    xmlHTTP.send();
};

