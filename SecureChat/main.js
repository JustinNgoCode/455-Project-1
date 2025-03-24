const { app, BrowserWindow } = require('electron');
const path = require('path');

function createWindow() {
    console.log('Creating window...');
    const win = new BrowserWindow({
        width: 800,
        height: 600,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false,
            webSecurity: false,
        },
    });
    win.loadFile('index.html').then(() => {
        console.log('Loaded index.html successfully');
    }).catch(err => {
        console.error('Error loading index.html:', err);
    });
    // Uncomment the following line to open DevTools for debugging
    // win.webContents.openDevTools();
    return win;
}

app.whenReady().then(() => {
    console.log('App is ready');
    createWindow(); // Window for User1
    createWindow(); // Window for User2
    createWindow(); // Window for User3

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});
