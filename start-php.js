const { spawn } = require('child_process');
const php = spawn('C:\php\php.exe', ['-S', 'localhost:8080', '-t', 'C:\Users\Prof\tedmark-digital'], { stdio: 'inherit' });
php.on('exit', (code) => process.exit(code));
