module.exports = {
  apps : [{
    name: 'asistencia-realtime',
    script: 'php',
    args: '-d opcache.enable=0 artisan asistencia:daemon',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G'
  }]
};

