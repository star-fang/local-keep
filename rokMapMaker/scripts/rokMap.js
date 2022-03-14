import AVL from "./tree/avl.js";

const SHAPE_CIRCLE = 0;
const SHAPE_RECT = 1;
const VERTEX_CODE_GATE = 0;
const VERTEX_CODE_CAMP = 1;
const VERTEX_CODE_KEEP = 2;

const LANGUAGE_CODE_KOR = 0;
const LANGUAGE_CODE_ENG = 1;

export default class RokMap {

    constructor(serverNumber, vertexData) {

        this.serverNo = serverNumber;
        this.allyAvl = new AVL();
        this.languageCode = LANGUAGE_CODE_KOR;
        this.unscribeVertex = null;
        this.unscribeAlly = null;
        this.watchAvl = new AVL();
        this.snackbar = $('#snackbar');
        this.vertexData = vertexData;

    }

    clear() {
        var searchBox = $('#searchBox').detach();
        $('#mapDisplay').empty().append(searchBox);
        $(".menu__content").empty();
    }

    draw() {
        this.initMap();
        this.initLands();
        this.initVertices();
    }

    loadLocalStorage() {
        if (localStorage) {
            const mapStr = localStorage.getItem(`map${this.serverNo}`);
            if (mapStr) {
                this.bindMap(JSON.parse(mapStr));
            }
        }
    }


    loadData() {
        return loadFromJSON(`data/map${this.serverNo}.json`).then(this.bindMap.bind(this)).catch(function (e) {
            console.log(e);
        });
    } // initData

    bindMap(map) {
        const vertexAvl = new AVL();
        const landAvl = new AVL();
        const nameAvl = new AVL();
        if (!map) return;
        this.size = map.size;

        map.names.forEach(function (name) {
            nameAvl.insert({ key: name.id, data: name });
        });

        const keepInfo = this.vertexData.keeps;
        map.keeps.forEach(function (keep, i) {
            keep.id = 770000 + i;
            Object.assign(keep, keepInfo);
            vertexAvl.insert({ key: keep.id, data: keep });
        });

        const campInfo = this.vertexData.camps;
        map.camps.forEach(function (baulur, i) {
            baulur.id = 390000 + i;
            Object.assign(baulur, campInfo);
            vertexAvl.insert({ key: baulur.id, data: baulur });

        });

        const gateInfo = this.vertexData.gates;
        map.gates.forEach(function (gate) {
            gate.shape = gateInfo.shape;
            gate.width = gateInfo.width;
            gate.name = gateInfo.name;
            gate.vc = gateInfo.vc;
            gate.color = gateInfo.color[gate.level - 1];
            vertexAvl.insert({ key: gate.id, data: gate });
        });

        map.lands.forEach(function (land) {
            landAvl.insert({ key: land.id, data: land });
        });

        vertexAvl.inOrderCallback(function (node) {

            if (node != null && node.data != undefined && node.data != null) {
                node.data.hiddenLandCount = 0;
                node.data.landIds = [];
                landAvl.inOrderCallback(function (landNode) {
                    if (contains(landNode.data.boundary, node.data.y, node.data.x)) {
                        node.data.landIds.push(landNode.data.id);
                    }
                    return false;
                });
            }
            return false;
        });

        this.vertexAvl = vertexAvl;
        this.landAvl = landAvl;
        this.nameAvl = nameAvl;
    }


    initMap() {
        const size = this.size;
        const serverNo = this.serverNo;
        const mapDisplay = $('#mapDisplay');
        const mapSizeAttr = `width="${size.width}" height="${size.height}"`;
        let mapDisplayWidth = mapDisplay.width();
        let mapDisplayHeight = mapDisplay.height();
        const mapDisplaySizeAttr = `width="${mapDisplayWidth}" height="${mapDisplayHeight}"`;

        const mapContainer = $(`<div class="mapContainer" ${mapSizeAttr}></div>`).appendTo(mapDisplay);
        this.mapContainer = mapContainer;
        this.land2dCtx = $(`<canvas class = "map landCanvas" ${mapSizeAttr}></canvas>`).appendTo(mapContainer)[0].getContext('2d');
        this.vertex2dCtx = $(`<canvas class = "map vertexCanvas" ${mapSizeAttr}></canvas>`).appendTo(mapContainer)[0].getContext('2d');

        const cursorTrackDom = $(`<canvas class = "tracker cursorTracker" ${mapDisplaySizeAttr}></canvas>`).appendTo(mapDisplay)[0];
        const cursor2dCtx = cursorTrackDom.getContext('2d');
        const vertexTrackDom = $(`<canvas class = "tracker vertexTracker" ${mapDisplaySizeAttr}></canvas>`).appendTo(mapDisplay)[0];
        const vertexTrack2dCtx = vertexTrackDom.getContext('2d');
        const centerTrackDom = $(`<canvas class = "tracker centerTracker" width="100" height="100"></canvas>`).appendTo(mapDisplay)[0];
        const centerTrack2dCtx = centerTrackDom.getContext('2d');
        const axisXDom = $(`<canvas class = "tracker axisX" width="${mapDisplayWidth}" height="15"></canvas>`).appendTo(mapDisplay)[0];
        const axisX2dCtx = axisXDom.getContext('2d');
        const axisYDom = $(`<canvas class = "tracker axisY" width="${mapDisplayHeight}" height="15"></canvas>`).appendTo(mapDisplay)[0];
        const axisY2dCtx = axisYDom.getContext('2d');
        $(`<svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="tracker aim" viewBox="0 0 100 100"
            ><g class="aim__circle"
            ><circle class="aim__path-elapsed" cx="50" cy="50" r="50"
            ></circle></g></svg>`).appendTo(mapDisplay);
        const sP = $('#sSs');
        const xP = $('#xXx');
        const yP = $('#yYy');
        sP.html(serverNo);
        const vertexDialog = $('#vertexDialog');
        const vertexDialogTitle = vertexDialog.children('.vertexDialog__title');
        const vertexDialogKernel = vertexDialog.children('.vertexDialog__kernel');
        const closeDialogButton = vertexDialog.children('.vertexDialog__closeButton');
        closeDialogButton.unbind();
        closeDialogButton.on('click', function () {
            vertexDialogTitle.html('');
            vertexDialogKernel.empty();
            vertexDialog.removeClass('active');
            closeDialogButton.attr('hidden', true);
        });
        function onDisplayResized() {
            mapDisplayWidth = mapDisplay.width();
            mapDisplayHeight = mapDisplay.height();
            cursorTrackDom.width = mapDisplayWidth;
            cursorTrackDom.height = mapDisplayHeight;
            vertexTrackDom.width = mapDisplayWidth;
            vertexTrackDom.height = mapDisplayHeight;
            axisXDom.width = mapDisplayWidth;
            axisYDom.width = mapDisplayHeight;
            onMove();
            drawTracker();
        }

        $(window).resize(function () {
            document.documentElement.style.height = `initial`;
            setTimeout(() => {
                document.documentElement.style.height = `100%`;
                setTimeout(() => {
                    // this line prevents the content
                    // from hiding behind the address bar
                    window.scrollTo(0, 1);
                }, 500);
            }, 500);
            onDisplayResized();
        });

        const menuOpenButton = $("#menuOpenButton");

        const menuBar = $("#menuBar");
        menuOpenButton.unbind();
        menuOpenButton.on('click', function () {
            menuBar.one('transitionend', function (e) {
                onDisplayResized();
                console.log('transitionend')
            });
            if (menuBar.hasClass('active')) {
                menuBar.removeClass('active');
                mapDisplay.removeClass('showMenu');
                menuOpenButton.css({
                    "transform": "rotate(180deg)"
                });
            } else {
                menuBar.addClass('active');
                mapDisplay.addClass('showMenu');
                menuOpenButton.css({
                    "transform": "rotate(0deg)"
                });
            }
            return false;
        });





        



        const mapEngine = new PhysicsEngine(mapContainer, function () {
            drawTracker();
            onMove();
        });
        mapEngine.activateZoomModule(size, mapContainer.parent());
        mapEngine.activateDraggingModule(
            mapContainer.parent(),
            cursorAction);
        const myLocationButton = $("#myLocationButton");
        myLocationButton.unbind();
        myLocationButton.on('click', function (e) {
            e.preventDefault();
            mapEngine.smoothMove(0, 0);
            drawTracker();

            return false;
        });

        //let threedMode = false;
        const mapObjs = $('.map');
        const threedButton = $("#threedButton");
        threedButton.unbind();
        threedButton.on('click',function( e ) {
            e.preventDefault();
            if(  mapEngine.threedMode = this.innerHTML == '3D' ) {
                mapObjs.each(function() {
                   $(this).animate({  borderSpacing: 45 }, {
                    step: function(now,fx) {
                      $(this).css( 'transform', `perspective(1200px) rotateX(${now}deg) translate3d(0px, 0px, 0px)`);
                    },
                    duration:'slow'
                },'linear');
                
                } );
                this.innerHTML = '2D';
            } else {
                mapObjs.each(function() {
                    $(this).animate({  borderSpacing: 0 }, {
                     step: function(now,fx) {
                       $(this).css( 'transform', `perspective(1200px) rotateX(${now}deg) translate3d(0px, 0px, 0px)`);
                     },
                     duration:'slow'
                 },'linear');
                 
                 } );
                this.innerHTML = '3D';
            }
            
        });

        const collapsibleButtons = $(".collapsible");
        collapsibleButtons.unbind();
        collapsibleButtons.each(function (i) {
            const buttonJObj = $(this);
            buttonJObj.on('click', function () {
                buttonJObj.toggleClass('active');
                buttonJObj.next().toggle();
            })

        });

        const vertexAvl = this.vertexAvl;
        const watchAvl = this.watchAvl;

        function drawCursor(pos, coor) {
            cursor2dCtx.clearRect(0, 0, mapDisplayWidth, mapDisplayHeight);

            const scale = mapEngine.scale;
            const mapEnginePos = mapEngine.pos;

            pos.x *= scale.x;
            pos.y *= scale.y;
            pos.x += mapEnginePos.left;
            pos.y += mapEnginePos.top;

            cursor2dCtx.strokeStyle = 'red';
            cursor2dCtx.lineWidth = 5;
            cursor2dCtx.lineCap = 'round';

            cursor2dCtx.beginPath();
            cursor2dCtx.moveTo(pos.x, 0);
            cursor2dCtx.lineTo(pos.x, 12);
            cursor2dCtx.stroke();
            cursor2dCtx.closePath();

            cursor2dCtx.beginPath();
            cursor2dCtx.moveTo(0, pos.y);
            cursor2dCtx.lineTo(12, pos.y);
            cursor2dCtx.stroke();
            cursor2dCtx.closePath();

            cursor2dCtx.fillStyle = "red";
            cursor2dCtx.beginPath();
            cursor2dCtx.arc(pos.x, pos.y, 3, 0, 2 * Math.PI);
            cursor2dCtx.fill();
            cursor2dCtx.closePath();

            cursor2dCtx.font = '17px sans-serif';
            cursor2dCtx.fillStyle = 'white';
            cursor2dCtx.textAlign = "left";
            cursor2dCtx.textBaseline = "hanging"
            cursor2dCtx.fillText(`${coor.x},${coor.y}`, pos.x, pos.y);
        }

        function cursorAction(pos, click) {
            const coor = pos2Coord(pos);

            if (click) {
                clickMap(coor.x, coor.y);
            } else {
                drawCursor(pos, coor);
            }
        }

        function pos2Coord(pos) {
            pos.x = Math.floor(pos.x);
            pos.y = Math.ceil(pos.y);
            const coorY = size.height - pos.y;
            return {
                x: pos.x,
                y: coorY,
                validX: pos.x >= 0 && pos.x <= size.width,
                validY: coorY >= 0 && coorY <= size.height
            }
        }

        let centerTracking = false;
        let vertexKernelShown = false;
        function onMove() {
            
            const mapScale = mapEngine.scale;
            const mapPos = mapEngine.pos;

            if (centerTracking) {
                centerTrack2dCtx.clearRect(0, 0, centerTrackDom.width, centerTrackDom.height);
                centerTracking = false;
            }
            if (vertexKernelShown) {
                vertexDialogKernel.addClass('hide');
                vertexKernelShown = false;
            }

            const pos = {
                x: (mapDisplayWidth / 2 - mapPos.left) / mapScale.y,
                y: (mapDisplayHeight / 2 - mapPos.top) / mapScale.x
            };

            if( mapEngine.threedMode  ) {
                mapObjs.each(function() {
                    $(this).css( 'transform', `perspective(1200px) rotateX(45deg) translate3d(${pos.x /2 - mapDisplayWidth / 2 }px, 0px, 0px)`);
                });
            }

            const coor = pos2Coord(pos);

            if (!coor.validX || !coor.validY) {
                const halfWidth = mapDisplayWidth / 2;
                const halfHeight = mapDisplayHeight / 2;
                //a : ax = sqrt(ss) : dx
                // ax = a(dx) / sqrt(ss)
                const dx = size.width / 2 - coor.x;
                const dy = coor.y - size.height / 2;
                const ss = Math.sqrt(dx * dx + dy * dy);
                const directionX = dx / ss;
                const directionY = dy / ss;
                centerTrack2dCtx.strokeStyle = 'yellow';
                centerTrack2dCtx.lineWidth = '5';
                centerTrack2dCtx.lineCap = 'round';
                centerTrack2dCtx.beginPath();
                centerTrack2dCtx.moveTo(halfWidth + 14 * directionX, halfHeight + 14 * directionY);
                centerTrack2dCtx.lineTo(halfWidth + 36 * directionX, halfHeight + 36 * directionY);
                centerTrack2dCtx.stroke();
                centerTrack2dCtx.closePath();
                centerTracking = true;
            }



            axisY2dCtx.clearRect(0, 0, axisYDom.width, axisYDom.height);
            axisY2dCtx.font = '15px sans-serif';
            axisY2dCtx.fillStyle = 'white';
            axisY2dCtx.textAlign = "center";
            axisY2dCtx.textBaseline = "middle";


            const offsetY = Math.ceil(2 / mapScale.y) * 50; // 50, 100, 150, 200 ....
            const middleY = Math.floor(coor.y / offsetY) * offsetY;
            for (let startY = middleY; startY * mapScale.y + mapPos.top <= axisYDom.width; startY += offsetY) {
                axisY2dCtx.fillText(size.height - startY, startY * mapScale.y + mapPos.top, axisYDom.height / 2);
            }
            for (let startY = middleY; startY * mapScale.y + mapPos.top >= 0; startY -= offsetY) {
                axisY2dCtx.fillText(size.height - startY, startY * mapScale.y + mapPos.top, axisYDom.height / 2);
            }

            axisX2dCtx.clearRect(0, 0, axisXDom.width, axisXDom.height);
            axisX2dCtx.textAlign = 'center';
            axisX2dCtx.font = '15px sans-serif';
            axisX2dCtx.textBaseline = 'middle';
            axisX2dCtx.fillStyle = 'white';
            const offsetX = Math.ceil(2 / mapScale.x) * 50;
            const middleX = Math.floor(coor.x / offsetX) * offsetX;
            for (let startX = middleX; startX * mapScale.x + mapPos.left <= axisXDom.width; startX += offsetX) {
                axisX2dCtx.fillText(startX, startX * mapScale.x + mapPos.left, axisXDom.height / 2);
            }
            for (let startX = middleX; startX * mapScale.x + mapPos.left >= 0; startX -= offsetX) {
                axisX2dCtx.fillText(startX, startX * mapScale.x + mapPos.left, axisXDom.height / 2);
            }

            xP.html(coor.x);
            yP.html(coor.y);

        }


        function clickMap(coorX, coorY) {

            var stopTraversal = false;
            vertexAvl.inOrderCallback(function (node) {

                if (stopTraversal) {
                    return true;
                }

                const vertex = node.data;
                if (RokMap.checkVertexBoundary(vertex, coorX, coorY)) {
                    if (vertex.hiddenLandCount < vertex.landIds.length) {
                        showVertexDialog(vertex);
                        drawTracker();
                        stopTraversal = true;
                        return true;
                    }
                }

                return false;
            });
        } // click map

        let trackerExist = false;
        function drawTracker() {
            if (trackerExist) {
                vertexTrack2dCtx.clearRect(0, 0, mapDisplayWidth, mapDisplayHeight);
                trackerExist = false;
            }
            if (trackerExist = watchAvl.getSize() > 0) {
                vertexTrack2dCtx.strokeStyle = 'white';
                vertexTrack2dCtx.lineWidth = 2;
                const pos = mapEngine.pos;
                const scale = mapEngine.scale;
                watchAvl.inOrderCallback(function (node) {
                    const watch = node.data;
                    const watchPos = watch.engine.pos;
                    vertexTrack2dCtx.beginPath();
                    vertexTrack2dCtx.moveTo(pos.left + watch.x * scale.x, pos.top + (size.height - watch.y) * scale.y);
                    vertexTrack2dCtx.lineTo(watchPos.left + watch.centerX, watchPos.top + watch.centerY);
                    vertexTrack2dCtx.stroke();
                    vertexTrack2dCtx.closePath();
                    return false;
                });
            }
        } // drawTracker

        const searchName = this.searchName.bind(this);
        function showVertexDialog(vertex) {
            const scale = mapEngine.scale;
            cursorTrackDom.style.display = 'none';
            mapEngine.smoothMove(
                mapDisplay.width() / 2 - vertex.x * scale.x, 
                mapDisplay.height() / 2 - (size.height - vertex.y) * scale.y,
                function() {
                    cursorTrackDom.style.display = 'block';
                });
            vertexDialogKernel.empty();
            if (watchAvl.getSize() > 0) {
                const node = watchAvl.search(vertex.id);
                if (node != null) {
                    closeWatch(node.data);
                    drawTracker();
                    return;
                }
            }

            vertexDialogKernel.removeClass('hide');
            vertexKernelShown = true;
            vertexDialogTitle.html(`${searchName(vertex)} ${vertex.level ? `lv.${vertex.level}` : ''} ${vertex.x},${vertex.y}`);
            vertexDialogTitle.unbind();
            vertexDialogTitle.on('click', function () {
                showVertexDialog(vertex);
            });

            closeDialogButton.removeAttr('hidden');

            vertexDialog.addClass('active');


            return;

        } // end showVertexDialog
        onMove();
        return;
    } // initMap



    searchName(data) {
        let nameData = data.name;
        if (!nameData) {
            let nameNode;
            if (data.nameId && (nameNode = this.nameAvl.search(data.nameId)) != null) {
                nameData = nameNode.data;
            } else {
                return null;
            }
        }

        switch (this.languageCode) {
            case LANGUAGE_CODE_ENG:
                return nameData.eng;
                break;
            case LANGUAGE_CODE_KOR:
            default:
                return nameData.kor;
        }
    }

    initLands() {
        this.land2dCtx.clearRect(0, 0, this.size.width, this.size.height);
        const checkLandDiv = $('#checkLandDiv');

        this.landAvl.inOrderCallback(function (node) {
            const land = node.data;
            if (land != undefined && land != null) {
                this.drawLand(land);
                const thisMap = this;
                $(`<input type="checkbox" id="land${land.id}" name="land${land.id}" checked=true>`)
                    .appendTo(checkLandDiv).change(function () {
                        if (this.checked) {
                            thisMap.drawLand(land);
                            thisMap.drawVertices(true, land.id, false);
                        } else {
                            thisMap.clearLand(land);
                            thisMap.drawVertices(true, land.id, true);
                        }
                    });
                checkLandDiv.append($(`<label for="land${land.id}">${this.searchName(land)}</label><br>`));

            }
        }.bind(this));


    } // initLands



    clearLand(land) {
        if (land == null || land.boundary == undefined || land.boundary == null) {
            return;
        }
        const edgeArr = land.boundary, ctx = this.land2dCtx;
        ctx.fillStyle = "black";
        ctx.beginPath();
        ctx.moveTo(edgeArr[0].x, this.size.height - edgeArr[0].y);
        for (var i = 1, len = edgeArr.length; i <= len; i++) {
            ctx.lineTo(edgeArr[i % len].x, this.size.height - edgeArr[i % len].y);
        }
        ctx.fill();
        ctx.closePath();
    }

    drawLand(land) {
        if (land == null || land.boundary == undefined || land.boundary == null) {
            return;
        }
        const edgeArr = land.boundary, ctx = this.land2dCtx;
        ctx.fillStyle = land.color;
        ctx.beginPath();
        ctx.moveTo(edgeArr[0].x, this.size.height - edgeArr[0].y);
        for (var i = 1, len = edgeArr.length; i <= len; i++) {
            ctx.lineTo(edgeArr[i % len].x, this.size.height - edgeArr[i % len].y);
        }
        ctx.fill();
        ctx.closePath();

        ctx.font = "30px Nanum Gothic";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = 'white';
        ctx.fillText(this.searchName(land), land.center.x, this.size.height - land.center.y);

    }

    initVertices() {
        const legendDiv = $('#legendDiv');
        const legendCanvasSize = `width="28" height="28"`;

        const vertexData = this.vertexData;
        Object.keys(vertexData).forEach(function (key) {
            const v = vertexData[key];
            switch (v.vc) {
                case VERTEX_CODE_CAMP:
                    let legendCamp = {};
                    Object.assign(legendCamp, v);
                    legendCamp.x = 14;
                    legendCamp.y = 14;
                    legendCamp.width = 14;
                    RokMap.drawVertex(legendCamp, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                    legendDiv.append($(`<label for="check_camp">${this.searchName(v)}</label>`));
                    const check_camp = $(`<input type="checkbox" id="check_camp" name="check_camp" checked=true><br>`).appendTo(legendDiv);
                    check_camp.change(function () {
                        if (check_camp.prop('checked')) {
                            this.drawVertices(false, VERTEX_CODE_CAMP, false);
                        } else {
                            this.drawVertices(false, VERTEX_CODE_CAMP, true);
                        }
                    }.bind(this));
                    break;
                case VERTEX_CODE_KEEP:
                    let legendKeep = {};
                    Object.assign(legendKeep, v);
                    legendKeep.x = 14;
                    legendKeep.y = 14;
                    legendKeep.width = 14;
                    RokMap.drawVertex(legendKeep, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                    legendDiv.append($(`<label for="check_keep">${this.searchName(v)}</label>`));
                    const check_keep = $(`<input type="checkbox" id="check_keep" name="check_keep" checked=true><br>`).appendTo(legendDiv);
                    check_keep.change(function () {
                        if (check_keep.prop('checked')) {
                            this.drawVertices(false, VERTEX_CODE_KEEP, false);
                        } else {
                            this.drawVertices(false, VERTEX_CODE_KEEP, true);
                        }
                    }.bind(this));
                    break;
                case VERTEX_CODE_GATE:
                    let legendGate = {};
                    Object.assign(legendGate, v);
                    legendGate.x = 14;
                    legendGate.y = 14;
                    legendGate.width = 14;
                    legendGate.color = 'black';
                    RokMap.drawVertex(legendGate, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                    legendDiv.append($(`<label">${this.searchName(v)}</label>`));
                    break;
            }
        }.bind(this));

        this.vertexAvl.inOrderCallback(function (node) {
            const vertex = node.data;
            RokMap.drawVertex(vertex, this.vertex2dCtx, this.size.height);
            return false;
        }.bind(this));
    }

    drawVertices(forLand, code, hide) {

        this.vertexAvl.inOrderCallback(function (node) {
            const vertex = node.data;
            if ((forLand && vertex.landIds.includes(code)) ||
                (!forLand && vertex.vc == code)) {
                vertex.hiddenLandCount += hide ? 1 : -1;
                RokMap.drawVertex(vertex, this.vertex2dCtx, this.size.height);
            }
            return false;
        }.bind(this));
    }

    static drawVertex(vertex, ctx, maxY) {

        if (maxY != undefined) {
            ctx.clearRect(
                vertex.x - vertex.width / 2
                , maxY - vertex.y - vertex.width / 2
                , vertex.width
                , vertex.width);
        }

        if (vertex.landIds == undefined || vertex.hiddenLandCount < vertex.landIds.length) {
            const vy = maxY != undefined ? maxY - vertex.y : vertex.y;
            ctx.fillStyle = vertex.color;
            ctx.beginPath();
            const radius = (vertex.width / 2) - 1;
            switch (vertex.shape) {
                case SHAPE_CIRCLE:
                    ctx.arc(vertex.x, vy, radius, 0, 2 * Math.PI);
                    break;
                case SHAPE_RECT:
                    ctx.moveTo(vertex.x - radius, vy + radius);
                    ctx.lineTo(vertex.x + radius, vy + radius);
                    ctx.lineTo(vertex.x + radius, vy - radius);
                    ctx.lineTo(vertex.x - radius, vy - radius);
                    ctx.lineTo(vertex.x - radius, vy + radius);
                    break;
            }
            ctx.fill();
            //ctx.stroke();
            ctx.closePath();
        }
    }


    static checkVertexBoundary(vertex, coorX, coorY) {
        const centerX = vertex.x, centerY = vertex.y, halfWidth = vertex.width / 2 + 5;
        switch (vertex.shape) {
            case SHAPE_CIRCLE:
                return Math.pow(centerX - coorX, 2) + Math.pow(centerY - coorY, 2) <= Math.pow(halfWidth, 2);
            case SHAPE_RECT:
                return centerX + halfWidth >= coorX
                    && centerX - halfWidth <= coorX
                    && centerY + halfWidth >= coorY
                    && centerY - halfWidth <= coorY;
            default:
                return false;
        }
    }


} // end class Map




