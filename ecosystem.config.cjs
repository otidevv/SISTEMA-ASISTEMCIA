module.exports = {
  apps : [
    {
      name: 'asistencia-realtime',
      script: 'php',
      args: '-d opcache.enable=0 artisan asistencia:daemon',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G'
    },
    {
      name: 'asistencia-queue',
      script: 'php',
      args: 'artisan queue:work --sleep=3 --tries=3',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '512M'
    },
    {
      name: 'asistencia-reverb',
      script: 'node',
      script: 'reverb.cjs',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '512M'
    }
  ]
};

