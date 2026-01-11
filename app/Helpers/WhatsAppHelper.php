<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Formatear número de teléfono a formato internacional (Perú +51)
     * 
     * @param string $phone
     * @return string
     */
    public static function formatPhoneNumber($phone)
    {
        // Eliminar espacios, guiones y paréntesis
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Si ya tiene el código de país +51, retornar
        if (str_starts_with($phone, '+51')) {
            return $phone;
        }
        
        // Si empieza con 51, agregar +
        if (str_starts_with($phone, '51')) {
            return '+' . $phone;
        }
        
        // Si es un número de 9 dígitos (celular peruano), agregar +51
        if (strlen($phone) === 9) {
            return '+51' . $phone;
        }
        
        // Si no cumple ningún formato, retornar con +51
        return '+51' . $phone;
    }

    /**
     * Generar enlace de WhatsApp con mensaje pre-escrito
     * 
     * @param string $phone
     * @param string $message
     * @return string
     */
    public static function generateLink($phone, $message)
    {
        $formattedPhone = self::formatPhoneNumber($phone);
        // Eliminar el + para el enlace de WhatsApp
        $phoneNumber = str_replace('+', '', $formattedPhone);
        
        // Codificar el mensaje para URL
        $encodedMessage = urlencode($message);
        
        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    }

    /**
     * Obtener plantilla de mensaje según el tipo
     * 
     * @param string $tipo
     * @param array $data
     * @return string
     */
    public static function getMessageTemplate($tipo, $data)
    {
        switch ($tipo) {
            case 'tema_pendiente':
                return self::temaPendienteTemplate($data);
            
            case 'falta':
                return self::faltaTemplate($data);
            
            case 'recordatorio':
                return self::recordatorioTemplate($data);
            
            default:
                return self::genericTemplate($data);
        }
    }

    /**
     * Plantilla para tema pendiente
     */
    private static function temaPendienteTemplate($data)
    {
        $docente = $data['docente_nombre'] ?? 'Docente';
        $curso = $data['curso'] ?? 'el curso';
        $fecha = $data['fecha'] ?? 'hoy';
        $hora = $data['hora'] ?? '';
        
        return "🔔 *Recordatorio - Tema Desarrollado Pendiente*\n\n" .
               "Estimado(a) *{$docente}*,\n\n" .
               "Le recordamos que aún no ha registrado el tema desarrollado para:\n\n" .
               "📚 *Curso:* {$curso}\n" .
               "📅 *Fecha:* {$fecha}\n" .
               "🕐 *Hora:* {$hora}\n\n" .
               "Por favor, ingrese al sistema y registre el tema desarrollado en su clase.\n\n" .
               "Gracias por su colaboración. 🙏";
    }

    /**
     * Plantilla para falta
     */
    private static function faltaTemplate($data)
    {
        $docente = $data['docente_nombre'] ?? 'Docente';
        $curso = $data['curso'] ?? 'el curso';
        $fecha = $data['fecha'] ?? 'hoy';
        $hora = $data['hora'] ?? '';
        
        return "⚠️ *Notificación de Inasistencia*\n\n" .
               "Estimado(a) *{$docente}*,\n\n" .
               "Se ha detectado que no registró asistencia para:\n\n" .
               "📚 *Curso:* {$curso}\n" .
               "📅 *Fecha:* {$fecha}\n" .
               "🕐 *Hora:* {$hora}\n\n" .
               "Si hubo algún inconveniente, por favor comuníquese con la coordinación académica.\n\n" .
               "Gracias.";
    }

    /**
     * Plantilla para recordatorio general
     */
    private static function recordatorioTemplate($data)
    {
        $docente = $data['docente_nombre'] ?? 'Docente';
        $mensaje = $data['mensaje'] ?? 'Tiene pendientes por completar en el sistema.';
        
        return "📢 *Recordatorio del Sistema de Asistencia*\n\n" .
               "Estimado(a) *{$docente}*,\n\n" .
               "{$mensaje}\n\n" .
               "Por favor, revise el sistema a la brevedad posible.\n\n" .
               "Gracias por su atención. 🙏";
    }

    /**
     * Plantilla genérica
     */
    private static function genericTemplate($data)
    {
        $docente = $data['docente_nombre'] ?? 'Docente';
        $mensaje = $data['mensaje'] ?? 'Tiene una notificación del sistema.';
        
        return "Estimado(a) {$docente},\n\n{$mensaje}";
    }
}
