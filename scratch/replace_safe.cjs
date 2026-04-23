const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    if (!fs.existsSync(dir)) return;
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

function replaceInFiles() {
    const dirs = ['public/js', 'resources/views', 'app/Http/Controllers'];
    
    dirs.forEach(dir => {
        walkDir(dir, function(filePath) {
            if (filePath.endsWith('.js') || filePath.endsWith('.blade.php') || filePath.endsWith('.php')) {
                let content = fs.readFileSync(filePath, 'utf8');
                // CASE SENSITIVE replacement of RENIEC (all caps)
                if (content.includes('RENIEC')) {
                    let newContent = content.replace(/RENIEC/g, 'Base de Datos');
                    fs.writeFileSync(filePath, newContent, 'utf8');
                    console.log('Replaced in:', filePath);
                }
            }
        });
    });
}

replaceInFiles();
