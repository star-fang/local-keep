/**
 * @param {string} collectionName 
 * @param {int} limit 
 * @param {function} callback 
 * @return {function} unscribe
 */
function linkStorage(collectionName, limit, callback) {

  var query = firebase.firestore()
    .collection(collectionName)
    //.where('channel', '==', getChannel())
    //.orderBy('timestamp', 'desc')
    .limit(limit);

  return query.onSnapshot(function (snapshot) {
    snapshot.docChanges().forEach(function (change) {
      callback(change);
    });
  });

}

function addCollection(collectionName, initFactor, callback, onerror) {
  const collection = firebase.firestore().collection(collectionName);

  collection.add(initFactor)
    .then(function (docRef) {
      console.log("new doc created : ", docRef.id);
      callback();
    })
    .catch(function (error) {
      console.error('Error adding new doc', error);
      onerror(error);
    });

}


function updateCollection(collectionName, id, factor, onsuccess, onerror) {

  const document = firebase.firestore().collection(collectionName).doc(id);
  let updatePromise;
  if (factor == null || Object.keys(factor).length === 0) { // delete
    updatePromise = document.delete();
  } else { // update
    updatePromise = document.set(factor, { merge: true });
  }
  updatePromise.then(onsuccess).catch(onerror);
}


