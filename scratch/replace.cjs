const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

function replaceInFiles() {
    const dirs = ['public/js', 'resources/views'];
    
    dirs.forEach(dir => {
        if (!fs.existsSync(dir)) return;
        walkDir(dir, function(filePath) {
            if (filePath.endsWith('.js') || filePath.endsWith('.blade.php')) {
                let content = fs.readFileSync(filePath, 'utf8');
                if (content.includes('RENIEC')) {
                    // Replace RENIEC with Base de Datos
                    let newContent = content.replace(/RENIEC/g, 'Base de Datos');
                    fs.writeFileSync(filePath, newContent, 'utf8');
                    console.log('Replaced in:', filePath);
                }
            }
        });
    });
}

replaceInFiles();
