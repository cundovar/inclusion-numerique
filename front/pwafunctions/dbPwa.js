import { openDB } from 'idb';
// --- DB IndexedDB pour PWA ---
const dbPromise = openDB('spa-db', 1, {
    upgrade(db) {
      db.createObjectStore('menus', { keyPath: 'id' });
      db.createObjectStore('categories', { keyPath: 'id' });
      db.createObjectStore('user', { keyPath: 'username' });
    }
  });
  async function saveMenusToDB(menusArray) {
    const db = await dbPromise;
    const tx = db.transaction('menus', 'readwrite');
    
    menusArray.forEach(item => {
      const pureItem = JSON.parse(JSON.stringify(item)); 
      tx.store.put(pureItem);
    });
    
    await tx.done;
  }
  
  
  
  async function saveCatsToDB(catsArray) {
    const db = await dbPromise;
    const tx = db.transaction('categories', 'readwrite');
    // convertion en données json
    catsArray.forEach(item => {
      const pureItem = JSON.parse(JSON.stringify(item)); 
      tx.store.put(pureItem);
    });
    
    await tx.done;
  }
  
  async function saveUserToDB(user) {
    const db = await dbPromise;
    // convertion en données json
    const tx = db.transaction('user', 'readwrite');
    const jsonUser = JSON.parse(JSON.stringify(user)); 
    tx.store.put(jsonUser);
    await tx.done;
  }
  
  // --- Chargement user dans IndexedDB pour le offline ---
  
  async function loadUserFromDB() {
    const db = await dbPromise;
    const user = await db.get('user', 1);
    return user;
  }
  
  async function loadMenusFromDB() {
    const db = await dbPromise;
    return db.getAll('menus');
  }
  
  async function loadCatsFromDB() {
    const db = await dbPromise;
    return db.getAll('categories');
  }
  