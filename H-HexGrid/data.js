var timeDivision = {
    timestamp: "2019-10-30 17:26",
    data: [
        {name:"0400", kor:"섬멸의 전장(4시)"},
        {name:"1000", kor:"난투의 전장(10시)"},
        {name:"1600", kor:"격전의 전장(16시)"},
        {name:"2200", kor:"혈투의 전장(22시)"}
    ]
};

var hexGridSizeInfo = {
    timestamp: "2020-02-20 17:50",
    data: [ 
        {type:"A", size:23.60},
        {type:"C", size:17.61},
        {type:"D", size:23.37},
        {type:"E", size:23.37},
        {type:"G", size:19.02}
    ]
};

function typeToHexGridSizeInfo(type) {
    var data = hexGridSizeInfo.data[hexGridSizeInfo.data.map(x => x.type).indexOf(type)];
    return data;
}
  

var vertexData = {
    timestamp: "2019-10-21 05:19",
    dataMap: [
        { size: "XL", radius: 35, kor: "초대형", color: "yellow" },
        { size: "L", radius: 30, kor: "대형", color: "green" },
        { size: "M", radius: 25, kor: "중형", color: "orange" },
        { size: "S", radius: 20, kor: "소형", color: "blue" },
        { size: "XS", radius: 15, kor: "초소형", color: "red" } 
    ]
};

var terrainData = {
    timestamp: "2020-02-20 15:46",
    data : [
        { hex: '#c0c0c0', id:0,  terrain: '성내', altitude: '고', movable: true},
        { hex: '#7a7a7a', id:1,  terrain: '성내', altitude: '최고', movable: true},
        { hex: '#ff6a00', id:2,  terrain: '사막', altitude: '중', movable: true},
        { hex: '#ffe96d', id:3,  terrain: '황무지', altitude: '중', movable: true},
        { hex: '#ffe97f', id:4,  terrain: '황무지', altitude: '고', movable: true},
        { hex: '#ffdb3d', id:5,  terrain: '황무지', altitude: '최고', movable: true},
        { hex: '#0094ff', id:6,  terrain: '물', altitude: '중', movable: false},
        { hex: '#000000', id:7,  terrain: '안개', altitude: '중', movable: false},
        { hex: '#7f0000', id:8,  terrain: '성벽', altitude: '중', movable: false},
        { hex: '#351500', id:9,  terrain: '절벽', altitude: '중', movable: false},
        { hex: '#74cd5d', id:10,  terrain: '평지', altitude: '중', movable: true},
        { hex: '#4cff55', id:11,  terrain: '초원', altitude: '중', movable: true},
        { hex: '#b6ff00', id:12,  terrain: '초원', altitude: '고', movable: true},
        { hex: '#00ffff', id:13,  terrain: '완류', altitude: '중', movable: true},
        { hex: '#006d9d', id:14,  terrain: '물', altitude: '중', movable: false},
        { hex: '#1c6937', id:15,  terrain: '습지', altitude: '중', movable: true},
        { hex: '#007f0e', id:16,  terrain: '숲', altitude: '중', movable: true},
        { hex: '#00a74a', id:17,  terrain: '숲', altitude: '고', movable: true},
        { hex: '#ff6a4e', id:18,  terrain: '다리', altitude: '중', movable: true},
        { hex: '#007f46', id:19,  terrain: '산지', altitude: '고', movable: true},
        { hex: '#846d52', id:20,  terrain: '잔도', altitude: '고', movable: true},
        { hex: '#745d40', id:21,  terrain: '잔도', altitude: '최고', movable: true},
    ]
};

  var facData = {
     timestamp: "2020-02-13 15:00",
      data : [
        { hex: '#00137f', id:101, terrain: '궁노대', movable: false, fac:'tower'},
        { hex: '#ff00dc', id:102, terrain: '성채', movable: false, fac:'castle'},
        { hex: '#0026ff', id:103, terrain: '막사', movable: false, fac:'tent'}, 
        { hex: '#037f60', id:104, terrain: '병영', movable: false, fac:'barrack'}
      ]
  };

function hexToTerrain(hex) {
    console.log(hex);
    var data = terrainData.data[terrainData.data.map(x => x.hex).indexOf(hex)];
    if( data == null ) {
        data = facData.data[facData.data.map(x => x.hex).indexOf(hex)];
    }
    return data;
}


var graphData = {
    timestamp: "2020-02-23 22:21",
    mode: "EXTENDED",
    criteria: { width: 900, height: 600 },
    edges: [
        { id: "R2", outV: "900", inV: "926" }, { id: "S2", outV: "900", inV: "918" }, { id: "R3", outV: "901", inV: "921" },
        { id: "S3", outV: "901", inV: "902" }, { id: "T3", outV: "901", inV: "932" }, { id: "R4", outV: "902", inV: "901" },
        { id: "S4", outV: "902", inV: "922" }, { id: "T4", outV: "902", inV: "943" }, { id: "R5", outV: "903", inV: "941" },
        { id: "S5", outV: "903", inV: "914" }, { id: "R6", outV: "904", inV: "910" }, { id: "S6", outV: "904", inV: "942" },
        { id: "T6", outV: "904", inV: "922" }, { id: "R7", outV: "905", inV: "941" }, { id: "S7", outV: "905", inV: "936" },
        { id: "R8", outV: "906", inV: "923" }, { id: "S8", outV: "906", inV: "933" }, { id: "T8", outV: "906", inV: "920" },
        { id: "R9", outV: "907", inV: "935" }, { id: "S9", outV: "907", inV: "911" }, { id: "R10", outV: "908", inV: "931" },
        { id: "S10", outV: "908", inV: "937" }, { id: "T10", outV: "908", inV: "910" }, { id: "U10", outV: "908", inV: "942" },
        { id: "R11", outV: "909", inV: "921" }, { id: "S11", outV: "909", inV: "912" }, { id: "T11", outV: "909", inV: "928" },
        { id: "R12", outV: "910", inV: "908" }, { id: "S12", outV: "910", inV: "904" }, { id: "T12", outV: "910", inV: "911" },
        { id: "R13", outV: "911", inV: "915" }, { id: "S13", outV: "911", inV: "929" }, { id: "T13", outV: "911", inV: "910" },
        { id: "U13", outV: "911", inV: "907" }, { id: "R14", outV: "912", inV: "914" }, { id: "S14", outV: "912", inV: "909" },
        { id: "T14", outV: "912", inV: "928" }, { id: "U14", outV: "912", inV: "926" }, { id: "R15", outV: "913", inV: "935" },
        { id: "S15", outV: "913", inV: "938" }, { id: "R16", outV: "914", inV: "903" }, { id: "S16", outV: "914", inV: "940" },
        { id: "T16", outV: "914", inV: "912" }, { id: "R17", outV: "915", inV: "919" }, { id: "S17", outV: "915", inV: "939" },
        { id: "T17", outV: "915", inV: "911" }, { id: "R18", outV: "916", inV: "923" }, { id: "S18", outV: "916", inV: "917" },
        { id: "T18", outV: "916", inV: "920" }, { id: "R19", outV: "917", inV: "916" }, { id: "S19", outV: "917", inV: "919" },
        { id: "T19", outV: "917", inV: "939" }, { id: "R20", outV: "918", inV: "900" }, { id: "S20", outV: "918", inV: "930" },
        { id: "R21", outV: "919", inV: "917" }, { id: "S21", outV: "919", inV: "915" }, { id: "R22", outV: "920", inV: "916" },
        { id: "S22", outV: "920", inV: "939" }, { id: "T22", outV: "920", inV: "906" }, { id: "U22", outV: "920", inV: "933" },
        { id: "R23", outV: "921", inV: "925" }, { id: "S23", outV: "921", inV: "934" }, { id: "T23", outV: "921", inV: "909" },
        { id: "U23", outV: "921", inV: "901" }, { id: "R24", outV: "922", inV: "904" }, { id: "S24", outV: "922", inV: "935" },
        { id: "T24", outV: "922", inV: "902" }, { id: "R25", outV: "923", inV: "916" }, { id: "S25", outV: "923", inV: "906" },
        { id: "R26", outV: "924", inV: "941" }, { id: "S26", outV: "924", inV: "936" }, { id: "T26", outV: "924", inV: "940" },
        { id: "R27", outV: "925", inV: "927" }, { id: "S27", outV: "925", inV: "934" }, { id: "T27", outV: "925", inV: "921" },
        { id: "R28", outV: "926", inV: "900" }, { id: "S28", outV: "926", inV: "912" }, { id: "R29", outV: "927", inV: "925" },
        { id: "S29", outV: "927", inV: "936" }, { id: "T29", outV: "927", inV: "940" }, { id: "R30", outV: "928", inV: "912" },
        { id: "S30", outV: "928", inV: "909" }, { id: "R31", outV: "929", inV: "937" }, { id: "S31", outV: "929", inV: "911" },
        { id: "R32", outV: "930", inV: "932" }, { id: "S32", outV: "930", inV: "918" }, { id: "R33", outV: "931", inV: "933" },
        { id: "S33", outV: "931", inV: "936" }, { id: "T33", outV: "931", inV: "937" }, { id: "U33", outV: "931", inV: "908" },
        { id: "R34", outV: "932", inV: "930" }, { id: "S34", outV: "932", inV: "943" }, { id: "T34", outV: "932", inV: "901" },
        { id: "R35", outV: "933", inV: "906" }, { id: "S35", outV: "933", inV: "920" }, { id: "T35", outV: "933", inV: "931" },
        { id: "R36", outV: "934", inV: "925" }, { id: "S36", outV: "934", inV: "921" }, { id: "T36", outV: "934", inV: "942" },
        { id: "R37", outV: "935", inV: "907" }, { id: "S37", outV: "935", inV: "922" }, { id: "T37", outV: "935", inV: "913" },
        { id: "U37", outV: "935", inV: "938" }, { id: "R38", outV: "936", inV: "905" }, { id: "S38", outV: "936", inV: "924" },
        { id: "T38", outV: "936", inV: "927" }, { id: "U38", outV: "936", inV: "931" }, { id: "R39", outV: "937", inV: "929" },
        { id: "S39", outV: "937", inV: "931" }, { id: "T39", outV: "937", inV: "908" }, { id: "R40", outV: "938", inV: "935" },
        { id: "S40", outV: "938", inV: "913" }, { id: "T40", outV: "938", inV: "943" }, { id: "R41", outV: "939", inV: "917" },
        { id: "S41", outV: "939", inV: "920" }, { id: "T41", outV: "939", inV: "915" }, { id: "R42", outV: "940", inV: "924" },
        { id: "S42", outV: "940", inV: "927" }, { id: "T42", outV: "940", inV: "914" }, { id: "R43", outV: "941", inV: "905" },
        { id: "S43", outV: "941", inV: "924" }, { id: "T43", outV: "941", inV: "903" }, { id: "R44", outV: "942", inV: "904" },
        { id: "S44", outV: "942", inV: "908" }, { id: "T44", outV: "942", inV: "934" }, { id: "R45", outV: "943", inV: "902" },
        { id: "S45", outV: "943", inV: "932" }, { id: "T45", outV: "943", inV: "938" }],
    vertices: [
        { id: "900", name: "흥고", size: "XS", type: "G", mx: 208, my: 504, hex: "#b5808b" }, 
        { id: "901", name: "무릉", size: "S", type: "B", mx: 433, my: 433, hex: "#f0d50c", namepos:1 }, 
        { id: "902", name: "장사", size: "M", type: "I", mx: 529, my: 410, hex: "#d10a18", namepos:5 }, 
        { id: "903", name: "서평", size: "S", type: "D", mx: 122, my: 196,hex: "#ff6a00", namepos:7 }, 
        { id: "904", name: "여강", size: "S", type: "E", mx: 616, my: 303, hex: "#99d88a", namepos:5 }, 
        { id: "905", name: "가정", size: "S", type: "C", mx: 222, my: 140, hex: "#4cff00" }, 
        { id: "906", name: "상당", size: "XS", type: "F", mx: 415, my: 110, hex: "#337146" }, 
        { id: "907", name: "오", size: "M", type: "E", mx: 784, my: 295, hex: "#5252aa" }, 
        { id: "908", name: "허창", size: "L", type: "G", mx: 517, my: 252, hex: "#c4cc2e", namepos:7 }, 
        { id: "909", name: "영안", size: "S", type: "C", mx: 286, my: 366, hex: "#b7b0a3" }, 
        { id: "910", name: "수춘", size: "M", type: "A", mx: 618, my: 246, hex: "#4661db", namepos:5 }, 
        { id: "911", name: "하비", size: "L", type: "I", mx: 747, my: 203, hex: "#21e47f", namepos:1 }, 
        { id: "912", name: "성도", size: "L", type: "I", mx: 153, my: 366, hex: "#ccb000" }, 
        { id: "913", name: "회계", size: "S", type: "A", mx: 754, my: 408, hex: "#2f7c1f", namepos:1 }, 
        { id: "914", name: "자동", size: "S", type: "G", mx: 160, my: 302, hex: "#3bcc77", namepos:5 }, 
        { id: "915", name: "북해", size: "XS", type: "C", mx: 711, my: 121, hex: "#7f1f00" }, 
        { id: "916", name: "계", size: "S", type: "C", mx: 560, my: 52, hex: "#c93e18" }, 
        { id: "917", name: "북평", size: "M", type: "F", mx: 647, my: 48, hex: "#8cae4e" }, 
        { id: "918", name: "교지", size: "S", type: "E", mx: 291, my: 513, hex: "#00b2a6" }, 
        { id: "919", name: "양평", size: "XS", type: "L", mx: 746, my: 50, hex: "#8caeD9" }, 
        { id: "920", name: "업", size: "L", type: "A", mx: 499, my: 122, hex: "#d9ff3e" }, 
        { id: "921", name: "양양", size: "L", type: "E", mx: 376, my: 359, hex: "#7f1078", namepos:7 }, 
        { id: "922", name: "시상", size: "M", type: "G", mx: 585, my: 365, hex: "#8fd3d1", namepos:5 }, 
        { id: "923", name: "진양", size: "XS", type: "G", mx: 439, my: 58, hex: "#b200ff" }, 
        { id: "924", name: "천수", size: "S", type: "A", mx: 202, my: 188, hex: "#ffd800", namepos:7 }, 
        { id: "925", name: "완", size: "S", type: "G", mx: 404, my: 282, hex: "#84ba4e" }, 
        { id: "926", name: "운남", size: "M", type: "F", mx: 92, my: 475, hex: "#0c52ba" }, 
        { id: "927", name: "상용", size: "XS", type: "B", mx: 338, my: 287, hex: "#84ba4e", namepos:7 }, 
        { id: "928", name: "강주", size: "M", type: "A", mx: 212, my: 426, hex: "#2bb2ba", namepos:5 }, 
        { id: "929", name: "소패", size: "XS", type: "F", mx: 652, my: 179, hex: "#21087f", namepos:1 }, 
        { id: "930", name: "울림", size: "XS", type: "C", mx: 369, my: 475, hex: "#5de81c" },
        { id: "931", name: "낙양", size: "XL", type: "I", mx: 440, my: 199, hex: "#52cdd5", namepos:7 }, 
        { id: "932", name: "영릉", size: "M", type: "A", mx: 431, my: 521, hex: "#231fae", namepos:5 }, 
        { id: "933", name: "하내", size: "S", type: "L", mx: 389, my: 159, hex: "#ff2342" },
        { id: "934", name: "강릉", size: "M", type: "A", mx: 454, my: 333, hex: "#cd590c", namepos:5 }, 
        { id: "935", name: "건업", size: "L", type: "B", mx: 707, my: 329, hex: "#ff7Caa" }, 
        { id: "936", name: "장안", size: "L", type: "D", mx: 325, my: 197, hex: "#0026ff" }, 
        { id: "937", name: "복양", size: "M", type: "E", mx: 566, my: 191, hex: "#dc8024",namepos:11 }, 
        { id: "938", name: "건안", size: "S", type: "F", mx: 655, my: 445, hex: "#aa5597", namepos:5 }, 
        { id: "939", name: "남피", size: "M", type: "G", mx: 576, my: 123, hex: "#004a7f", namepos:5 }, 
        { id: "940", name: "한중", size: "M", type: "F", mx: 229, my: 242, hex: "#ce1e4d", namepos:1 }, 
        { id: "941", name: "무위", size: "M", type: "I", mx: 99, my: 118, hex: "#ff0000" }, 
        { id: "942", name: "강하", size: "S", type: "B", mx: 530, my: 316, hex: "#d6516b", namepos:1 }, 
        { id: "943", name: "계양", size: "XS", type: "E", mx: 525, my: 483, hex: "#3ae18c" }]
};

