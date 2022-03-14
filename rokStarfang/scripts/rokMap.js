import AVL from "./tree/avl.js";

const SHAPE_CIRCLE = 0;
const SHAPE_RECT = 1;
const VERTEX_CODE_GATE = 0;
const VERTEX_CODE_CAMP = 1;
const VERTEX_CODE_KEEP = 2;

const LANGUAGE_CODE_KOR = 0;
const LANGUAGE_CODE_ENG = 1;

export default class RokMap {
    /** 
     * 2020-12-23 classPrivateProperies and classPrivateMethos and classProperties(static) are not yet supported by ios 
    */
    constructor(serverNumber) {

        this.serverNo = serverNumber;
        this.allyAvl = new AVL();
        this.languageCode = LANGUAGE_CODE_KOR;
        this.unscribeVertex = null;
        this.unscribeAlly = null;
        this.syncTimer = null;
        this.syncAvl = new AVL();
        this.watchAvl = new AVL();
        this.snackbar = $('#snackbar');
    }

    draw() {
        this.initMap();
        this.initLands();
        this.initVertices();
    }

    isOnLink() {
        return this.syncTimer != null;
    }

    link() {
        if (this.syncTimer != null) {
            this.unlink();
        }

        let currTime = Math.floor(Date.now() / UNIT_MILLISECONDS);
        const SYNC_CYCLE = 5;
        this.syncTimer = setInterval(() => {
            if (this.syncAvl.getSize() > 0) {
                this.syncAvl.inOrderCallback(function (node) {
                    const timer = node.data;
                    if (timer.syncAction(currTime) <= 0) {
                        this.syncAvl.delete(timer.id);
                        timer.timeup();
                    }
                    return false;
                }.bind(this));
            }
            if (currTime++ % SYNC_CYCLE == 0) {
                currTime = Math.floor(Date.now() / UNIT_MILLISECONDS);
            }
        }, UNIT_MILLISECONDS);

        this.unscribeVertex = linkStorage(
            `${this.serverNo}vertex`
            , this.vertexAvl.getSize()
            , this.updateVertexInfo.bind(this)
        );

    }

    unlink() {
        if (this.unscribeVertex != null) {
            this.unscribeVertex();
            this.unscribeVertex = null;
            console.log("unlink vertex");
        }
        if (this.unscribeAlly != null) {
            this.unscribeAlly();
            this.unscribeAlly = null;
            console.log("unlink ally");
        }
        if (this.syncTimer != null) {
            clearInterval(this.syncTimer);
            this.syncTimer = null;
        }

        if (this.syncAvl.getSize() > 0) {
            this.syncAvl.inOrderCallback(function (node) {
                const timer = node.data;
                delete timer.deadline;
                timer.removeVisualizer();
                return false;
            }.bind(this));
            this.syncAvl.root = null;
            this.syncAvl.size = 0;
        }


    }



    loadData() {

        return loadFromJSON('data/name.json').then(function (names) {
            const nameAvl = new AVL();
            if (names != null)
                names.forEach(function (name,) {
                    nameAvl.insert({ key: name.id, data: name });
                });
            this.nameAvl = nameAvl;
            return loadFromJSON(`data/mapOf${this.serverNo}.json`);
        }.bind(this)).then(function (map) {
            const vertexAvl = new AVL();
            const landAvl = new AVL();
            if (map != null) {
                this.size = map.size;
                map.keeps.forEach(function (keep, i) {
                    keep.id = 770000 + i;
                    keep.nameId = 124;
                    keep.shape = SHAPE_CIRCLE;
                    keep.width = 30;
                    keep.color = '#e3242b';
                    keep.vc = VERTEX_CODE_KEEP;
                    vertexAvl.insert({ key: keep.id, data: keep });
                });

                map.camps.forEach(function (baulur, i) {
                    baulur.id = 390000 + i;
                    baulur.nameId = 123;
                    baulur.shape = SHAPE_CIRCLE;
                    baulur.width = 20;
                    baulur.color = '#fce205';
                    baulur.vc = VERTEX_CODE_CAMP;
                    vertexAvl.insert({ key: baulur.id, data: baulur });

                });

                map.gates.forEach(function (gate) {
                    gate.shape = SHAPE_RECT;
                    gate.width = 25;
                    gate.nameId = 125;
                    gate.vc = VERTEX_CODE_GATE;
                    switch (gate.level) {
                        case 1:
                            gate.color = '#07da63';
                            break;
                        case 2:
                            gate.color = '#1167ba';
                            break;
                        case 3:
                            gate.color = '#52307c';
                            break;
                        default:
                            gate.color = '#ff6347';
                    }
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

            } // if map != null
            this.vertexAvl = vertexAvl;
            this.landAvl = landAvl;
        }.bind(this));

    } // initData


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
        const xP = $('#xXx');
        const yP = $('#yYy');
        const vertexDialog = $('#vertexDialog');
        const vertexDialogTitle = vertexDialog.children('.vertexDialog__title');
        const vertexDialogKernel = vertexDialog.children('.vertexDialog__kernel');
        const closeDialogButton = vertexDialog.children('.vertexDialog__closeButton');
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
        mapDisplay.on('transitionend webkitTransitionEnd oTransitionEnd', onDisplayResized);

        const menuOpenButton = $("#menuOpenButton");

        const menuBar = $("#menuBar");
        menuOpenButton.on('click', function () {
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

        })


        function smoothMove(left, top) {
            mapEngine.pos = { left: left, top: top };
            mapContainer.animate({
                left: `${left}px`,
                top: `${top}px`
            });
            onMove();
        }



        const mapEngine = new PhysicsEngine(mapContainer, function () {
            drawTracker();
            onMove();
        });
        mapEngine.activateZoomModule(size, mapContainer.parent());
        mapEngine.activateDraggingModule(
            mapContainer.parent(),
            cursorAction);

        $("#myLocationButton").on('click', function (e) {
            e.preventDefault();
            smoothMove(0, 0); dddd
            drawTracker();

            return false;
        });

        $(".collapsible").each(function (i, coll) {
            coll.addEventListener("click", function () {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });

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
            smoothMove(mapDisplay.width() / 2 - vertex.x * scale.x, mapDisplay.height() / 2 - (size.height - vertex.y) * scale.y);
            const timer = vertex.timer != undefined ? vertex.timer : null;
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
            if (timer != null) {
                timer.attachTo(vertexDialogKernel);
            }

            vertexDialog.addClass('active');


            return;

            const watch = $(`<div id="watch${vertex.id}" class="base-watch unselectable">
            </div>`).appendTo($('body'));





            if (timer != null) {
                timer.attachTo(watch);
            }
            watch.x = vertex.x;
            watch.y = vertex.y;
            watchAvl.insert({ key: vertex.id, data: watch });

            const mapPos = mapEngine.pos;
            const mapScale = mapEngine.scale;
            const watchTop = mapPos.top + (size.height - watch.y) * mapScale.y;
            const watchLeft = mapPos.left + watch.x * mapScale.x;
            watch.centerX = watch.outerWidth() / 2;
            watch.centerY = watch.outerHeight() / 2;

            const watchEngine = new PhysicsEngine(watch, drawTracker, { top: watchTop, left: watchLeft });

            watch.engine = watchEngine;
            watchEngine.activateDraggingModule(null);



            function closeWatch(watch) {
                if (timer != null) {
                    timer.detach(watch);
                }
                watchAvl.delete(vertex.id);
                watch.empty();
                watch.remove();
            }


        } // end showVertexDialog
        onMove();
        return;
    } // initMap



    searchName(data) {
        if (data != undefined && data != null && data.nameId != undefined) {
            const nameNode = this.nameAvl.search(data.nameId);
            if (nameNode != null && nameNode.data != undefined) {
                switch (this.languageCode) {
                    case LANGUAGE_CODE_ENG:
                        return nameNode.data.eng;
                        break;
                    case LANGUAGE_CODE_KOR:
                    default:
                        return nameNode.data.kor;
                }
            }
        }
        return null;
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
        for (var i = 1; i <= edgeArr.length; i++) {
            ctx.lineTo(edgeArr[i % edgeArr.length].x, this.size.height - edgeArr[i % edgeArr.length].y);
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
        for (var i = 1; i <= edgeArr.length; i++) {
            ctx.lineTo(edgeArr[i % edgeArr.length].x, this.size.height - edgeArr[i % edgeArr.length].y);
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
        let baulurCount = 0;
        let keepCount = 0;
        let gateCount = 0;
        const legendDiv = $('#legendDiv');
        const legendCanvasSize = `width="28" height="28"`;
        const thisContext = this;

        this.vertexAvl.inOrderCallback(function (node) {

            const vertex = node.data;
            RokMap.drawVertex(vertex, this.vertex2dCtx, this.size.height);


            switch (vertex.vc) {
                case VERTEX_CODE_CAMP:
                    vertex.timer = new Timer(`${vertex.id}`, `${this.serverNo}`);
                    if (baulurCount++ == 0) {
                        let legendCamp = {};
                        Object.assign(legendCamp, vertex);
                        legendCamp.x = 14;
                        legendCamp.y = 14;
                        legendCamp.width = 14;
                        RokMap.drawVertex(legendCamp, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                        legendDiv.append($(`<label for="check_camp">${this.searchName(vertex)}</label>`));
                        const check_camp = $(`<input type="checkbox" id="check_camp" name="check_camp" checked=true><br>`).appendTo(legendDiv);
                        check_camp.change(function () {
                            if (this.checked) {
                                thisContext.drawVertices(false, VERTEX_CODE_CAMP, false);
                            } else {
                                thisContext.drawVertices(false, VERTEX_CODE_CAMP, true);
                            }
                        });
                    }
                    break;
                case VERTEX_CODE_KEEP:
                    vertex.timer = new Timer(`${vertex.id}`, `${this.serverNo}`);
                    if (keepCount++ == 0) {
                        let legendKeep = {};
                        Object.assign(legendKeep, vertex);
                        legendKeep.x = 14;
                        legendKeep.y = 14;
                        legendKeep.width = 14;
                        RokMap.drawVertex(legendKeep, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                        legendDiv.append($(`<label for="check_keep">${this.searchName(vertex)}</label>`));
                        const check_keep = $(`<input type="checkbox" id="check_keep" name="check_keep" checked=true><br>`).appendTo(legendDiv);
                        check_keep.change(function () {
                            if (this.checked) {
                                thisContext.drawVertices(false, VERTEX_CODE_KEEP, false);
                            } else {
                                thisContext.drawVertices(false, VERTEX_CODE_KEEP, true);
                            }
                        });
                    }
                    break;
                case VERTEX_CODE_GATE:
                    if (gateCount++ == 0) {
                        let legendGate = {};
                        Object.assign(legendGate, vertex);
                        legendGate.x = 14;
                        legendGate.y = 14;
                        legendGate.width = 14;
                        legendGate.color = 'white';
                        RokMap.drawVertex(legendGate, $(`<canvas ${legendCanvasSize}></canvas>`).appendTo(legendDiv)[0].getContext('2d'));
                        legendDiv.append($('<label> ' + '관문' + '</label><br>'));
                    }
                    break;
            } // switch


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
        const timeVisualizer = (
            vertex.timer != undefined
            && vertex.timer != null
            && vertex.timer.vertexVisualizer != undefined) ? vertex.timer.vertexVisualizer : null;

        if (vertex.landIds == undefined || vertex.hiddenLandCount < vertex.landIds.length) {
            if (timeVisualizer != null) {
                timeVisualizer.removeAttr('hidden');
            }
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
        } else if (timeVisualizer != null) {
            timeVisualizer.attr('hidden', true);
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

    updateVertexInfo(change) {
        if (change.doc != undefined && change.doc != null) {
            const node = this.vertexAvl.search(change.doc.id);
            const changeData = change.doc.data();
            if (node != null) {
                const vertex = node.data;
                if (vertex.timer
                    && typeof changeData.timeLimit == 'number'
                    && typeof changeData.deadline == 'number') {
                    const timer = vertex.timer;
                    const isFirstLink = typeof timer.deadline == 'undefined';
                    const isRemoved = change.type === 'removed';

                    timer.deadline = isRemoved ? 0 : changeData.deadline;
                    timer.timeLimit = changeData.timeLimit;
                    const leftTime = timer.syncAction();
                    if (!isFirstLink) {
                        let message;
                        if (isRemoved) {
                            message = '연결 해제';
                        } else {
                            message = '시간 변경';
                        }
                        this.showSnackbar(`${this.searchName(vertex)} (${vertex.x},${vertex.y}) ${message}`);
                    }

                    if (isRemoved) {
                        timer.removeVisualizer();
                    } else if (leftTime >= 0) {
                        this.syncAvl.insert({ key: vertex.id, data: timer });
                        const timeRemainingColor = vertex.vc == VERTEX_CODE_CAMP ? 'white' : 'black';
                        if (timer.vertexVisualizer == null) {
                            const vertexVisualizer = timer.makeVisualizer(
                                this.mapContainer,
                                vertex.hiddenLandCount >= vertex.landIds.length,
                                timeRemainingColor);
                            const width = vertex.width + 10;
                            vertexVisualizer.css({
                                width: `${width}px`,
                                height: `${width}px`,
                                top: `${this.size.height - vertex.y - width / 2}px`,
                                left: `${vertex.x - width / 2}px`

                            });
                        }
                    }
                } // if time information included
            } // if change data is valid
        } // if change doc is valid
    } // change vertex info

    showSnackbar(message) {

        this.snackbar[0].MaterialSnackbar.showSnackbar({
            message: message,
            timeout: 1500
        });
        return false;

    }


} // end class Map




