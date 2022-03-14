import RokMap from "./rokMap.js";

window.onload = function () {
  //localStorage.clear();

  if (indexedDB) {
    //this.alert( 'indexedDB supported');
  } else {
    this.alert('indexedDB not supported');
  }
  loadData().then(function (vertices) {
    start(vertices);
  });
} // end window.onload

function loadData() {
  return loadFromJSON('data/vertex.json');
}

function start(vertexInfo) {

  let defaultMap = null;

  const newButton = $('#newButton');
  const loadButton = $('#loadButton');
  const saveButton = $('#saveButton');

  newButton.on('click', function () {
    const form = $('<form></form>');
    $('<label for="server">서버:</label>').appendTo(form);
    const input_server = $('<input class="dialog__input" type="number" name="server"><br>').appendTo(form);
    $('<label for="width">크기:</label>').appendTo(form);
    const input_width = $('<input class="dialog__input" type="number" name="width">').appendTo(form);
    $('<label for="height"> x </label>').appendTo(form);
    const input_height = $('<input class="dialog__input" type="number" name="height">').appendTo(form);

    $(form).dialog({
      title: 'NEW',
      modal: true,
      buttons: {
        'OK': function () {
          const serverInput = input_server.val();
          const widthInput = input_width.val();
          const heightInput = input_height.val();
          const defaultData = {
            gates: [],
            keeps: [],
            lands: [],
            camps: [],
            names: [],
            size: {
              "width": widthInput,
              "height": heightInput
            }
          };

          form.empty();
          form.dialog('close');
          localStorage.setItem(`map${serverInput}`, JSON.stringify(defaultData));
          if (defaultMap != null) {
            defaultMap.clear();
          }
          defaultMap = new RokMap(serverInput, vertexInfo);
          defaultMap.loadLocalStorage();
          defaultMap.draw();
        },
        'Cancel': function () {
          form.empty();
          $(this).dialog('close');
        }
      }
    });

    const newServerButton = form.siblings('.ui-dialog-buttonpane').find('button:first');
    newServerButton.hide();
    input_server.on('input', checkNewInput);
    input_width.on('input', checkNewInput);
    input_height.on('input', checkNewInput);
    let okButtonHided = true;
    function checkNewInput() {
      const serverInput = input_server.val();
      const widthInput = input_width.val();
      const heightInput = input_height.val();

      if (serverInput > 0 && widthInput > 0 && heightInput > 0) {

        if (localStorage[serverInput] != undefined || localStorage[serverInput] != null) {
          if (!okButtonHided) {
            newServerButton.hide();
            okButtonHided = true;
          }
        } else {
          if (okButtonHided) {
            newServerButton.show();
            okButtonHided = false;
          }
        }
      } else if (!okButtonHided) {
        newServerButton.hide();
        okButtonHided = true;
      }
    }
  });




  saveButton.on('click', function () {

  });


  loadButton.on('click', function () {

    const ul = $('<ul></ul>');
    if( localStorage ) {
    const localstorageKeyList = Object.keys(localStorage).filter(function(key) {
      return key.includes('map');
    });
    console.log( localstorageKeyList );
    const localStorageKeyListSize = localstorageKeyList.length;
    if( localStorageKeyListSize > 0 ) {
      $('<span>로컬 저장소</span>').appendTo(ul);
      localstorageKeyList.forEach( function(key) {
        const serverNumber = key.replace(/[^\d]/g, '');
        const li = $(`<li>${serverNumber}</li>`).appendTo(ul);
        $('<button>load</button>').appendTo(li).on('click', function () {
          if (defaultMap != null) {
            defaultMap.clear();
          }
          defaultMap = new RokMap(serverNumber, vertexInfo);
          defaultMap.loadLocalStorage();
          defaultMap.draw();
          ul.dialog('close');
        });
        $('<button><i class="material-icons">delete_forever</i></button>').appendTo(li).on('click', function () {
          if( confirm(`delete ${serverNumber} of local storage?`) ) {
            localStorage.removeItem(key);
            li.remove();
          }
        });
      })
    }
   }
    
    $.ajax({
      url: 'data/',
      success: function (data) {
        console.log(data);
        $('<span>맵 파일</span>').appendTo(ul);
        $(data).find("a:contains(map)").each(function () {
          const serverNumber = $(this).text().replace(/[^\d]/g, '');
          const li = $(`<li>${serverNumber}</li>`).appendTo(ul);
          $('<button>load</button>').appendTo(li).on('click', function () {
            if (defaultMap != null) {
              defaultMap.clear();
            }
            defaultMap = new RokMap(serverNumber, vertexInfo);
            defaultMap.loadData().then(function () {
              defaultMap.draw();
              ul.dialog('close');
            }).catch(function (e) {
              console.log(e);
              alert('로딩 실패');
            });
          })
        });
        ul.dialog();
      }
    });

    
    return false;
  });


} // end start
