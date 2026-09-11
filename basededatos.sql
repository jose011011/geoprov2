DROP DATABASE IF EXISTS marketplace_servicios_lp_V2;

CREATE DATABASE marketplace_servicios_lp_V2
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE marketplace_servicios_lp_V2;

-- =====================================================
-- 1. CONTROL DE ACCESO Y USUARIOS (RBAC)
-- =====================================================
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    celular VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    token_recuperacion VARCHAR(255) NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) 
        REFERENCES roles(id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_usuarios_correo ON usuarios(correo);
CREATE INDEX idx_usuarios_celular ON usuarios(celular);

-- =====================================================
-- 2. CLIENTES
-- =====================================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    direccion_referencia VARCHAR(255) NOT NULL,
    zona VARCHAR(100) NOT NULL,
    latitud_predeterminada DECIMAL(10,8) NULL,
    longitud_predeterminada DECIMAL(11,8) NULL,
    CONSTRAINT fk_cliente_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 3. PLANES DE MEMBRESÍA Y MONETIZACIÓN (FIXGEO)
-- =====================================================
CREATE TABLE planes_suscripcion (
    id_plan INT AUTO_INCREMENT PRIMARY KEY,
    nombre_plan VARCHAR(50) NOT NULL UNIQUE, -- 'GRATUITO_TOKENS', 'BASICO_MENSUAL', 'PREMIUM_DESTACADO'
    precio_mensual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tokens_mensuales INT NOT NULL DEFAULT 5,
    posicionamiento_destacado TINYINT(1) NOT NULL DEFAULT 0,
    descripcion TEXT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- =====================================================
-- 4. CATEGORÍAS Y SERVICIOS
-- =====================================================
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    tipo_clasificacion ENUM('TECNICO', 'EMPIRICO_OFICIO', 'AMBOS') NOT NULL DEFAULT 'AMBOS',
    icono_fa VARCHAR(50) DEFAULT 'fa-solid fa-wrench',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 5. PROVEEDORES (TÉCNICOS Y EMPÍRICOS)
-- =====================================================
CREATE TABLE profesionales (
    id_profesional INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    id_categoria INT NOT NULL,
    id_plan INT NOT NULL DEFAULT 1,
    tipo_prestador ENUM('TECNICO_PROFESIONAL', 'OFICIO_EMPIRICO') NOT NULL DEFAULT 'TECNICO_PROFESIONAL',
    tipo_documento_identidad ENUM('CI', 'NIT', 'EXTRANJERO') NOT NULL DEFAULT 'CI',
    numero_documento VARCHAR(30) NOT NULL UNIQUE,
    experiencia_anios INT NOT NULL DEFAULT 0,
    descripcion_servicio TEXT NOT NULL,
    macrodistrito_base ENUM('ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES', 'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO') NOT NULL,
    zona_especifica VARCHAR(150) NOT NULL,
    tokens_disponibles INT NOT NULL DEFAULT 5,
    fin_suscripcion DATE NULL,
    tarifa_base DECIMAL(10,2) NULL DEFAULT 0.00,
    estado_validacion ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    estado_disponibilidad ENUM('DISPONIBLE', 'OCUPADO', 'NO_DISPONIBLE') NOT NULL DEFAULT 'NO_DISPONIBLE',
    latitud_actual DECIMAL(10,8) NULL,
    longitud_actual DECIMAL(11,8) NULL,
    ultima_conexion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_profesional_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_profesional_categoria FOREIGN KEY (id_categoria) 
        REFERENCES categorias(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_profesional_plan FOREIGN KEY (id_plan) 
        REFERENCES planes_suscripcion(id_plan) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_prof_disponibilidad ON profesionales(estado_disponibilidad, estado_validacion);
CREATE INDEX idx_prof_distrito ON profesionales(macrodistrito_base);

-- =====================================================
-- 6. HISTORIAL DE PAGOS DE MEMBRESÍAS Y RECARGA TOKENS
-- =====================================================
CREATE TABLE transacciones_suscripcion (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_profesional INT NOT NULL,
    id_plan INT NULL,
    tipo_transaccion ENUM('MEMBRESIA_MENSUAL', 'PAQUETE_TOKENS') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) DEFAULT 'QR_SIMPLE_BOLIVIA',
    codigo_comprobante VARCHAR(100) NOT NULL UNIQUE,
    estado_pago ENUM('PENDIENTE', 'CONFIRMADO', 'FALLIDO') DEFAULT 'CONFIRMADO',
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaccion_profesional FOREIGN KEY (id_profesional) 
        REFERENCES profesionales(id_profesional) ON DELETE CASCADE,
    CONSTRAINT fk_transaccion_plan FOREIGN KEY (id_plan) 
        REFERENCES planes_suscripcion(id_plan) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- 7. DOCUMENTOS DE VERIFICACIÓN
-- =====================================================
CREATE TABLE documentos_profesional (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_profesional INT NOT NULL,
    tipo_documento_archivo ENUM(
        'CI_ANVERSO',
        'CI_REVERSO',
        'TITULO_TECNICO',
        'CERTIFICADO_ANTECEDENTES',
        'REFERENCIA_LABORAL',
        'OTRO'
    ) NOT NULL DEFAULT 'CI_ANVERSO',
    archivo_url VARCHAR(255) NOT NULL,
    estado_revision ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    observacion VARCHAR(255) NULL,
    revisado_por INT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision TIMESTAMP NULL,
    CONSTRAINT fk_doc_profesional FOREIGN KEY (id_profesional) 
        REFERENCES profesionales(id_profesional) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_doc_revisor FOREIGN KEY (revisado_por) 
        REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- 8. SOLICITUDES DE SERVICIO
-- =====================================================
CREATE TABLE solicitudes_servicio (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    codigo_seguimiento VARCHAR(20) NOT NULL UNIQUE,
    id_cliente INT NOT NULL,
    id_profesional INT NOT NULL,
    descripcion_problema TEXT NOT NULL,
    direccion_servicio VARCHAR(255) NOT NULL,
    macrodistrito ENUM('ZONA_SUR', 'SOPOCACHI', 'CENTRO', 'MIRAFLORES', 'SAN_PEDRO', 'COTAHUMA', 'PERIFERICA', 'EL_ALTO') NOT NULL,
    zona VARCHAR(100) NOT NULL,
    latitud_destino DECIMAL(10,8) NOT NULL,
    longitud_destino DECIMAL(11,8) NOT NULL,
    estado_servicio ENUM(
        'PENDIENTE',
        'ACEPTADA',
        'EN_CAMINO',
        'EN_PROCESO',
        'FINALIZADA',
        'CANCELADA'
    ) NOT NULL DEFAULT 'PENDIENTE',
    precio_acordado DECIMAL(10,2) NULL,
    tiempo_estimado_llegada_min INT NULL,
    motivo_cancelacion VARCHAR(255) NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio_atencion TIMESTAMP NULL,
    fecha_finalizacion TIMESTAMP NULL,
    CONSTRAINT fk_solicitud_cliente FOREIGN KEY (id_cliente) 
        REFERENCES clientes(id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_profesional FOREIGN KEY (id_profesional) 
        REFERENCES profesionales(id_profesional) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_solicitud_estado ON solicitudes_servicio(estado_servicio);

-- =====================================================
-- 9. SEGUIMIENTO GPS EN TIEMPO REAL (HISTORIAL / TELEMETRÍA)
-- =====================================================
CREATE TABLE tracking_solicitud_gps (
    id_tracking BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_profesional INT NOT NULL,
    latitud DECIMAL(10,8) NOT NULL,
    longitud DECIMAL(11,8) NOT NULL,
    velocidad_kmh DECIMAL(5,2) NULL DEFAULT 0.00,
    timestamp_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tracking_solicitud FOREIGN KEY (id_solicitud) 
        REFERENCES solicitudes_servicio(id_solicitud) ON DELETE CASCADE,
    CONSTRAINT fk_tracking_profesional FOREIGN KEY (id_profesional) 
        REFERENCES profesionales(id_profesional) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_tracking_reciente ON tracking_solicitud_gps(id_solicitud, timestamp_registro DESC);

-- =====================================================
-- 10. MENSAJERÍA DIRECTA CON ADJUNTOS (FOTOS)
-- =====================================================
CREATE TABLE mensajes (
    id_mensaje BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_remitente INT NOT NULL,
    tipo_mensaje ENUM('TEXTO', 'IMAGEN', 'SISTEMA') NOT NULL DEFAULT 'TEXTO',
    mensaje TEXT NULL,
    archivo_adjunto VARCHAR(255) NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensaje_solicitud FOREIGN KEY (id_solicitud) 
        REFERENCES solicitudes_servicio(id_solicitud) ON DELETE CASCADE,
    CONSTRAINT fk_mensaje_remitente FOREIGN KEY (id_remitente) 
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 11. CALIFICACIONES Y REPUTACIÓN MULTIDIMENSIONAL
-- =====================================================
CREATE TABLE calificaciones (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL UNIQUE,
    puntuacion_general INT NOT NULL,
    puntualidad INT NOT NULL DEFAULT 5,
    calidad_trabajo INT NOT NULL DEFAULT 5,
    comentario TEXT NULL,
    fecha_calificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_puntuacion_gen CHECK (puntuacion_general BETWEEN 1 AND 5),
    CONSTRAINT chk_puntualidad CHECK (puntualidad BETWEEN 1 AND 5),
    CONSTRAINT chk_calidad CHECK (calidad_trabajo BETWEEN 1 AND 5),
    CONSTRAINT fk_calificacion_solicitud FOREIGN KEY (id_solicitud) 
        REFERENCES solicitudes_servicio(id_solicitud) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 12. AUDITORÍA INALTERABLE DEL SISTEMA
-- =====================================================
CREATE TABLE auditoria_logs (
    id_log BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT NULL,
    valores_anteriores JSON NULL,
    valores_nuevos JSON NULL,
    ip_origen VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_auditoria_fecha ON auditoria_logs(fecha_evento DESC);







#A. Triggers de Seguridad y Lógica de Negocio


DELIMITER //

-- Trigger 1: Impedir que un profesional NO APROBADO o NO DISPONIBLE reciba o acepte solicitudes
CREATE TRIGGER trg_validar_profesional_solicitud
BEFORE INSERT ON solicitudes_servicio
FOR EACH ROW
BEGIN
    DECLARE v_estado_val ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO');
    DECLARE v_disponibilidad ENUM('DISPONIBLE', 'OCUPADO', 'NO_DISPONIBLE');
    
    SELECT estado_validacion, estado_disponibilidad 
    INTO v_estado_val, v_disponibilidad
    FROM profesionales 
    WHERE id_profesional = NEW.id_profesional;
    
    IF v_estado_val != 'APROBADO' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no ha sido validado documentalmente.';
    END IF;
    
    IF v_disponibilidad != 'DISPONIBLE' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no se encuentra en estado DISPONIBLE.';
    END IF;
END;
//

-- Trigger 2: Auditoría automática ante cambios de estado en solicitudes
CREATE TRIGGER trg_audit_solicitud_estado
AFTER UPDATE ON solicitudes_servicio
FOR EACH ROW
BEGIN
    IF OLD.estado_servicio <> NEW.estado_servicio THEN
        INSERT INTO auditoria_logs (
            id_usuario,
            accion,
            tabla_afectada,
            registro_id,
            valores_anteriores,
            valores_nuevos,
            ip_origen
        ) VALUES (
            NULL,
            CONCAT('CAMBIO_ESTADO_', NEW.estado_servicio),
            'solicitudes_servicio',
            NEW.id_solicitud,
            JSON_OBJECT('estado_anterior', OLD.estado_servicio),
            JSON_OBJECT('estado_nuevo', NEW.estado_servicio, 'codigo', NEW.codigo_seguimiento),
            '127.0.0.1'
        );
    END IF;
END;
//

DELIMITER ;










#B. Evento Programado: Purga de Chats a los 15 Días de Finalizado
DELIMITER //

CREATE EVENT IF NOT EXISTS evt_purgar_mensajes_antiguos
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Elimina mensajes de solicitudes finalizadas o canceladas hace más de 15 días
    DELETE m FROM mensajes m
    INNER JOIN solicitudes_servicio s ON m.id_solicitud = s.id_solicitud
    WHERE s.estado_servicio IN ('FINALIZADA', 'CANCELADA')
      AND s.fecha_finalizacion < DATE_SUB(NOW(), INTERVAL 15 DAY);
END;
//

DELIMITER ; 



#C. Vistas SQL para el Panel del Super Administrador
-- Vista 1: Cuadro de Mando de Reputación y Rendimiento de Profesionales
CREATE OR REPLACE VIEW vw_metricas_profesionales AS
SELECT 
    p.id_profesional,
    CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
    u.correo,
    u.celular,
    c.nombre_categoria,
    p.tipo_prestador,
    p.macrodistrito_base,
    p.estado_validacion,
    p.estado_disponibilidad,
    pl.nombre_plan,
    COUNT(s.id_solicitud) AS total_servicios_atendidos,
    COALESCE(ROUND(AVG(cal.puntuacion_general), 2), 5.00) AS promedio_estrellas,
    COALESCE(ROUND(AVG(cal.puntualidad), 2), 5.00) AS promedio_puntualidad,
    COALESCE(ROUND(AVG(cal.calidad_trabajo), 2), 5.00) AS promedio_calidad
FROM profesionales p
INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
INNER JOIN categorias c ON p.id_categoria = c.id_categoria
INNER JOIN planes_suscripcion pl ON p.id_plan = pl.id_plan
LEFT JOIN solicitudes_servicio s ON p.id_profesional = s.id_profesional AND s.estado_servicio = 'FINALIZADA'
LEFT JOIN calificaciones cal ON s.id_solicitud = cal.id_solicitud
GROUP BY p.id_profesional, u.nombre, u.apellido, u.correo, u.celular, c.nombre_categoria, p.tipo_prestador, p.macrodistrito_base, p.estado_validacion, p.estado_disponibilidad, pl.nombre_plan;

-- Vista 2: Log de Auditoría Reciente con Nombres de Usuario
CREATE OR REPLACE VIEW vw_auditoria_detallada AS
SELECT 
    a.id_log,
    a.fecha_evento,
    COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'SISTEMA_AUTO') AS responsable,
    r.nombre_rol,
    a.accion,
    a.tabla_afectada,
    a.registro_id,
    a.valores_anteriores,
    a.valores_nuevos,
    a.ip_origen
FROM auditoria_logs a
LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
LEFT JOIN roles r ON u.id_rol = r.id_rol
ORDER BY a.fecha_evento DESC;


#D. Datos Iniciales / Semilla Actualizados
INSERT INTO roles (id_rol, nombre_rol, descripcion) VALUES
(1, 'SUPER_ADMIN', 'Acceso total y configuración de auditoría'),
(2, 'ADMIN', 'Gestión operativa, validación documental y reportes'),
(3, 'PROFESIONAL', 'Técnicos calificados y trabajadores de oficios'),
(4, 'CLIENTE', 'Usuarios demandantes de servicios');

INSERT INTO planes_suscripcion (id_plan, nombre_plan, precio_mensual, tokens_mensuales, posicionamiento_destacado, descripcion) VALUES
(1, 'GRATUITO_TOKENS', 0.00, 5, 0, 'Asignación mensual de 5 tokens gratuitos. Pago por recarga si se agotan.'),
(2, 'BASICO_MENSUAL', 29.00, 40, 0, '40 tokens mensuales, insignia de verificación y posicionamiento estándar en La Paz.'),
(3, 'PREMIUM_DESTACADO', 69.00, 999, 1, 'Propuestas ilimitadas, prioridad de radio geográfico e insignia destacada.');

INSERT INTO categorias (id_categoria, nombre_categoria, slug, tipo_clasificacion, icono_fa, descripcion) VALUES
(1, 'Electricidad Residencial', 'electricidad', 'TECNICO', 'fa-bolt', 'Instalaciones, cableado, cortos y tableros eléctricos.'),
(2, 'Electrónica y Electrodomésticos', 'electronica', 'TECNICO', 'fa-tv', 'Reparación de TVs, refrigeradores y microondas.'),
(3, 'Plomería y Gasfitería', 'plomeria', 'AMBOS', 'fa-faucet-drip', 'Fugas de agua, desagües e instalaciones sanitarias.'),
(4, 'Cuidado del Hogar y Niñeras', 'cuidado-nineras', 'EMPIRICO_OFICIO', 'fa-baby-carriage', 'Atención de niños, personas mayores y apoyo doméstico.'),
(5, 'Albañilería y Obras Menores', 'albanileria', 'EMPIRICO_OFICIO', 'fa-trowel-bricks', 'Construcción, refacciones de muros y cerámica.');


-- Cambiamos el rol del usuario que acabas de crear al Rol 1 (SUPER_ADMIN)
UPDATE usuarios 
SET id_rol = 1 
WHERE correo = 'admin@geopro.com';

-- Opcional: Eliminar su registro de la tabla 'clientes' ya que ahora es administrador
DELETE FROM clientes 
WHERE id_usuario = (SELECT id_usuario FROM usuarios WHERE correo = 'admin@geopro.com');
























USE 



DELIMITER //

DROP TRIGGER IF EXISTS trg_validar_profesional_solicitud//

CREATE TRIGGER trg_validar_profesional_solicitud
BEFORE INSERT ON solicitudes_servicio
FOR EACH ROW
BEGIN
    DECLARE v_estado_val ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO');
    DECLARE v_disponibilidad ENUM('DISPONIBLE', 'OCUPADO', 'NO_DISPONIBLE');
    DECLARE v_fin_suscripcion DATE;
    DECLARE v_id_plan INT;

    SELECT estado_validacion, estado_disponibilidad, fin_suscripcion, id_plan
    INTO v_estado_val, v_disponibilidad, v_fin_suscripcion, v_id_plan
    FROM profesionales
    WHERE id_profesional = NEW.id_profesional;

    IF v_estado_val != 'APROBADO' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no ha sido validado documentalmente.';
    END IF;

    IF v_disponibilidad != 'DISPONIBLE' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Operación denegada: El prestador de servicio no se encuentra en estado DISPONIBLE.';
    END IF;

    -- El plan gratuito (id_plan = 1) no vence. Los planes pagos requieren fin_suscripcion vigente.
    IF v_id_plan != 1 THEN
        IF v_fin_suscripcion IS NULL OR v_fin_suscripcion < CURDATE() THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Operación denegada: La membresía del prestador de servicio se encuentra vencida.';
        END IF;
    END IF;
END;
//

DELIMITER ;










USE 



CREATE TABLE notificaciones (
    id_notificacion BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    url_destino VARCHAR(255) NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_notif_usuario_leida ON notificaciones(id_usuario, leida);



-- Ejecuta esto en phpMyAdmin (pestaña SQL)
ALTER TABLE planes_suscripcion 
CHANGE COLUMN precio_mensual precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,




CREATE TABLE clientes_favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_profesional INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY(id_cliente, id_profesional),
    CONSTRAINT fk_fav_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_fav_profesional FOREIGN KEY (id_profesional) REFERENCES profesionales(id_profesional) ON DELETE CASCADE
) ENGINE=INNODB;



