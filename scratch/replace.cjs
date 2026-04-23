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
                // Regex for case-insensitive 'RENIEC' as a whole word or in common phrases
                // We'll be careful to only replace what's likely a user-facing string or comment
                if (/RENIEC/i.test(content)) {
                    // Replace 'RENIEC' (any case) with 'Base de Datos'
                    // but we try to preserve some context if it's all caps
                    let newContent = content.replace(/RENIEC/gi, 'Base de Datos');
                    fs.writeFileSync(filePath, newContent, 'utf8');
                    console.log('Replaced in:', filePath);
                }
            }
        });
    });
}

replaceInFiles();
