function downloadObjectAsJson(exportObj, exportName) {
  var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportObj));
  var downloadAnchorNode = document.createElement('a');
  downloadAnchorNode.setAttribute("href", dataStr);
  downloadAnchorNode.setAttribute("download", exportName + ".json");
  document.body.appendChild(downloadAnchorNode); // required for firefox
  downloadAnchorNode.click();
  downloadAnchorNode.remove();
}

function downloadCanvasImage(fileName, canvasDOM) {
  var link = document.createElement('a');
      link.download = fileName;
      link.href = canvasDOM.toDataURL('image/png');
      link.click();
}

function getSearchParameters() {
  var prmstr = decodeURI(window.location.search.substr(1));
  return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
}

function transformToAssocArray(prmstr) {
  var params = {};
  var prmarr = prmstr.split("&");
  for (var i = 0; i < prmarr.length; i++) {
    var tmparr = prmarr[i].split("=");
    params[tmparr[0]] = tmparr[1];
  }
  return params;
}

function loadFromJSON(url) {

  return new Promise( function( resolve, reject) {
    $.ajax({
      type: "GET",
      url: url,
      success: function (response) {
        resolve( response );
      },
      error: function ( e) {
        reject( e);
      }
    });
  });
};

/*
* Ray-castring algorithm
* check points in polygon regardless of convex & concave
*
*/
function contains(bounds, lat, lng) {
  //https://rosettacode.org/wiki/Ray-casting_algorithm
  var count = 0;
  for (var b = 0; b < bounds.length; b++) {
      var vertex1 = bounds[b];
      var vertex2 = bounds[(b + 1) % bounds.length];
      if( vertex1.x == vertex2.x && vertex2.x == lng) {
        if( Math.max( vertex1.y, vertex2.y) > lat && Math.min( vertex1.y, vertex2.y ) < lat) return true; 
      } else if( vertex1.y == vertex2.y && vertex2.y == lat) {
        if( Math.max( vertex1.x, vertex2.x) > lng && Math.min( vertex1.x, vertex2.x ) < lng) return true; 
      }
      if (west(vertex1, vertex2, lng, lat))
          ++count;
  }
  return (count % 2) == 1;

  /**
   * @return {boolean} true if (x,y) is west of the line segment connecting A and B
   */
  function west(A, B, x, y) {
      if (A.y <= B.y) {
          if (y <= A.y || y > B.y ||
              x >= A.x && x >= B.x) {
              return false;
          } else if (x < A.x && x < B.x) {
              return true;
          } else {
              return (y - A.y) / (x - A.x) > (B.y - A.y) / (B.x - A.x);
          }
      } else {
          return west(B, A, x, y);
      }
  }
}

// end Ray-casting algorithm