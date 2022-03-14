'use strict';
const rt = Math.sqrt(3);
const path_map = 'map/';
const path_image = 'images/';
const path_fac = 'images/fac/';
var gridInfo;
var facInfo;


function getSearchParameters() {
  var prmstr = decodeURI(window.location.search.substr(1));
  return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
}

function transformToAssocArray( prmstr ) {
  var params = {};
  var prmarr = prmstr.split("&");
  for ( var i = 0; i < prmarr.length; i++) {
      var tmparr = prmarr[i].split("=");
      params[tmparr[0]] = tmparr[1];
  }
  return params;
  }

window.onload = function () {
  var params = getSearchParameters();
  loadFacImgs();
  var type = params.type;
  this.draw(type);
  if( indexedDB) {
   // this.alert( 'indexedDB supported');
  } else {
    this.alert( 'indexedDB not supported');
  }
} // end window.onload

function loadFacImgs() {
  facInfo = [];
  facData.data.forEach( function(element, index, array) {
    var facImg = new Image();
    facImg.src = path_fac + element.fac + '.png';
    facInfo[index] = {fac:element.fac,img:facImg};
  });
}



function draw(type) {
  var scale = {x:1,y:1};
  var a = typeToHexGridSizeInfo(type).size;
  var selectedPos = {};
  var size = {};
  var dragValue = {value:0};
  var focusLine = 'line';

  var tileImg = new Image();
  tileImg.crossOrigin = 'Anonymous';
  tileImg.src = path_map + 'type' + type + '_TILE.png';

  tileImg.onload = function() {

    const ctx_tile = drawTile( tileImg, size );
    var bgImg = new Image();
    bgImg.src = path_map +  'type' + type + '_BG.png';
    
    bgImg.onload = function() {

    drawBG( bgImg );

    const ctx_grid = $('#gridCanvas')[0].getContext('2d');
   
    drawGrid( a, size, ctx_grid, {block:false, fac:false} , ctx_tile );
    
    $('#gridCanvas').unbind();
    $('#gridCanvas').on('click',clickGrid );

    ScrollZoom($('#mapDiv'), 6.0, 0.1, scale, selectedPos, a);
    DragElement( $('#mapDiv'), dragValue, scale,selectedPos, a );

  $(document).unbind();
  $(document).keydown( function( e ) {
    if( !e ) {
      e = window.event;
    }
  
    var code = e.charCode ? e.charCode : e.keyCode;
    if( !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey ) {
      console.log( 'code',code );
      switch( code ) {
        case 65: // a
        if( selectedPos.x > 0) {
          selectedPos.x -= 1;
          }
          break;
          case 68: // d

          selectedPos.x += 1;
            break;
            case 87: // w
            if( selectedPos.y > 0 ) {
              selectedPos.y -= 1;
            }
            break;
            case 83: // s
            selectedPos.y += 1;
            break;
            default:
      }

      drawSelector( selectedPos, a, ctx_tile, focusLine, size );
    }
  } );

  prepareUtilities( a, size );
  
  }


  function clickGrid( e ) {
    console.log(dragValue.value);
    if( dragValue.value < 7) {
    const pos = getMousePos($('#gridCanvas')[0], e, scale);
    const index = getHexIndex( pos, a );
    selectedPos.x = index.x;
    selectedPos.y = index.y;
    drawSelector( selectedPos, a, ctx_tile, focusLine, size );
    }
    
  }

  }

} // end draw


function prepareUtilities( a, size, ctx_grid ) {

  var e = $.Event('keydown');


  $('#settingButton').unbind();
  $('#settingButton').on('click', function(e) {
      toggleDiv($('#settingButton'), $('#settingDiv') );
    } );
    

    $('#vkeyButton').unbind();
    $('#vkeyButton').on('click', function(e) {
      toggleDiv($('#vkeyButton'), $('#vkeyDiv') );
    } );


    HoldButton($('#vkeyW'), function() {
      e.keyCode = 87;
      $(document).trigger(e);
    });

    HoldButton($('#vkeyS'), function() {
      e.keyCode = 83;
      $(document).trigger(e);
    });

    HoldButton($('#vkeyA'), function() {
      e.keyCode = 65;
      $(document).trigger(e);
    });


    HoldButton($('#vkeyD'), function() {
      e.keyCode = 68;
      $(document).trigger(e);
    });

    $('#blockSwitch').unbind();
    $('#blockSwitch').on('click', function( e ) {
      drawGrid( a, size, ctx_grid, {block:$('#blockSwitch').prop('checked'), fac: $('#facSwitch').prop('checked')} );
    });

    $('#facSwitch').unbind();
    $('#facSwitch').on('click', function( e ) {
      drawGrid( a, size, ctx_grid, {block:$('#blockSwitch').prop('checked'), fac: $('#facSwitch').prop('checked')} );
    });
}

function toggleDiv( button, div ) {
  var counter = new Counter(30);
  if( button.prop('value') === 'open' ) {
    setIntervalX(function verticesFrame() {
      var left = (-300 + counter.getCount() * 10) + 'px';
      div.css({left: left});
        counter.countUp();
    }, 20, counter.getMax() + 1);
    button.prop('value','close');
    button.css({opacity: 0.5});
  } else {
    setIntervalX(function verticesFrame() {
      var left = ( counter.getCount() * (-10)) + 'px';
      div.css({left: left});
        counter.countUp();
    }, 20, counter.getMax() + 1);
    button.prop('value','open');
    button.css({opacity: 0.9});
  }
}

function drawTile( tileImg, size ) {
  size.width = tileImg.width;
  size.height = tileImg.height;
  const ctx_tile = $('#tileCanvas')[0].getContext('2d');
  $('#mapDiv').attr('width', size.width);
  $('#mapDiv').attr('height', size.height);
  $('#tileCanvas').attr('width', size.width);
  $('#tileCanvas').attr('height', size.height);
  $('#bgCanvas').attr('width', size.width);
  $('#bgCanvas').attr('height', size.height);
  $('#gridCanvas').attr('width', size.width);
  $('#gridCanvas').attr('height', size.height);
  ctx_tile.clearRect(0,0, size.width, size.height);
  ctx_tile.drawImage(tileImg, 0, 0, size.width, size.height);
  return ctx_tile;
}


function drawBG( bgImg ) {
  const ctx_bg = $('#bgCanvas')[0].getContext('2d');
  ctx_bg.clearRect(0,0, bgImg.width, bgImg.height);
  ctx_bg.drawImage(bgImg, 0, 0, bgImg.width, bgImg.height);
}


function drawGrid( a, size, ctx_grid, showOption, ctx_tile ) {
  var x = rt*a/2, y = a;

  ctx_grid.clearRect(0,0, size.width, size.height);
  ctx_grid.lineWidth = 1;

  if( ctx_tile !== undefined ) {
    gridInfo = [];
  }
  drawHexGrid( x, y, a, ctx_grid, size, 0, showOption, ctx_tile );

} // end draw


function drawHexGrid(x,y, a, ctx, size, row, showOption, ctx_tile ) {

if( x <= 0 || x >= size.width || y <= 0 || y >= size.height ) {
  return;
}

if( ctx_tile !== undefined ) {
  gridInfo[row] = [];
}

    drawHexRow( x, y, a, ctx, size.width, row ,showOption, ctx_tile );

    drawHexGrid( x + (row % 2 == 0 ? 1 : -1) * rt * a / 2, y + 3 * a / 2, a, ctx, size, row+1, showOption, ctx_tile );
    
}


function drawHexRow( x, y, a, ctx, mx, row, showOption, ctx_tile ) {
  var ix = x;
  for( var i = 0; ix < mx; i++ ) {
    if( ctx_tile !== undefined ) {
      var pixelData = ctx_tile.getImageData(ix,y,1,1).data;
      var r = pixelData[0], g = pixelData[1], b = pixelData[2];
      var info = hexToTerrain( rgbToHex(r,g,b) );
      gridInfo[row][i] = info;
    }

    if( showOption.block !== undefined && showOption.block === true && gridInfo[row][i] != undefined && !gridInfo[row][i].movable ) {
      ctx.strokeStyle = 'black';
      ctx.fillStyle = 'black';
      drawHex( ix, y, a, ctx, true );    
    } else {
      ctx.strokeStyle = 'gray';
      drawHex( ix, y, a, ctx );  
    }

    if( showOption.fac !== undefined && showOption.fac === true && gridInfo[row][i] != undefined && gridInfo[row][i].fac != null ) {
      //console.log('fac!!');

      var img = facInfo[facInfo.map(x => x.fac).indexOf(gridInfo[row][i].fac)].img;
      ctx.drawImage(img,ix - img.width / 2,y - img.height / 2 );
    }

        
    ix += rt * a;
  }
}

function drawHex( x, y, a, ctx, fill ) {
  ctx.beginPath();
  ctx.moveTo(
      x + a * Math.sin( 5 * Math.PI/3),
      y - a * Math.cos( 5 * Math.PI/3)
      );

  for( var i = 0; i < 5; i++ ) {
      ctx.lineTo(
          x + a * Math.sin( i * Math.PI/3 ),
          y - a * Math.cos( i * Math.PI/3)
          );
  }

  ctx.closePath();

  if( fill === undefined || fill === false) {
    ctx.stroke();
  } else {
    ctx.fill();
    ctx.stroke();
  }
  
}

function drawSelector( index, a, ctx_tile, focusLine, size ) {

  $('#pos').html(index.x +','+ index.y);

  const ctx = $('#gridSelector')[0].getContext("2d");
  $('#gridSelector').attr('width', size.width );
  $('#gridSelector').attr('height', size.height );
  ctx.clearRect(0,0, size.width, size.height);
  ctx.fillStyle = 'white';

  var maxIndex = getHexIndex({x:size.width,y:size.height}, a);
  var currentPos = getHexPos( index, a );

  var pixelData = ctx_tile.getImageData(currentPos.x,currentPos.y,1,1).data;
      var r = pixelData[0], g = pixelData[1], b = pixelData[2];
      var terrainData = hexToTerrain( rgbToHex(r,g,b) );

      if( terrainData != undefined ) {
        $('#terrain').html(  terrainData.terrain );
      } else {
        $('#terrain').html(  '??' );
      }

      

      if( focusLine !== undefined) {
      if( focusLine === 'hexa' ) {
  for( var ix = 0; ix < maxIndex.x; ix++) {
    var pos = getHexPos({x:ix, y:index.y}, a);
    drawHex( pos.x, pos.y, a, ctx,true);
  }

  for( var iy = 0; iy < maxIndex.y; iy++ ) {
    var pos = getHexPos({x:index.x, y:iy}, a);
    drawHex( pos.x, pos.y, a, ctx, true);
  }
} else if( focusLine === 'line' ) {
  ctx.strokeStyle = 'red';
  ctx.lineWidth = 3;
  ctx.moveTo( currentPos.x, 0);
  ctx.lineTo( currentPos.x, size.height);
  ctx.stroke();

  ctx.moveTo( 0, currentPos.y);
  ctx.lineTo( size.width, currentPos.y);
  ctx.stroke();


} 
      }

  

  ctx.fillStyle = '#ff9000';
  drawHex( currentPos.x, currentPos.y, a, ctx, true );

}

function getHexIndex( pos, a ) {
  var y = Math.round(( pos.y - a )*2/3/a);
  var x =(y % 2 == 0 )? Math.round(( pos.x -  rt * a / 2 ) / (rt * a)) :
  Math.round(( pos.x - rt * a ) / (rt * a));
  return {x,y};
}

function getHexPos( index, a) {
  var y = (index.y)*3*a/2 + a, x = (index.y % 2 == 0 )?(index.x + 1/2)*rt*a:(index.x + 1)*rt*a;
  return {x,y};
}

function componentToHex(c) {
  var hex = c.toString(16);
  return hex.length == 1 ? "0" + hex : hex;
}

function rgbToHex(r, g, b) {
  return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
}

