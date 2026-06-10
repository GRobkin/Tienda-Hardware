-- ============================================================
-- Tienda de Hardware — Script de base de datos
-- ============================================================

CREATE DATABASE IF NOT EXISTS tienda_hardware CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_hardware;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(60)  NOT NULL,
    apellido    VARCHAR(60)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    token       VARCHAR(40)  DEFAULT '',
    confirmado  TINYINT(1)   NOT NULL DEFAULT 0,
    admin       TINYINT(1)   NOT NULL DEFAULT 0,
    creado_en   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80)  NOT NULL,
    slug        VARCHAR(80)  NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT ''
);

INSERT INTO categorias (nombre, slug, descripcion) VALUES
    ('Componentes',        'componentes',        'CPU, GPU, RAM, almacenamiento y más'),
    ('Periféricos',        'perifericos',        'Teclados, mouse, auriculares y más'),
    ('Monitores',          'monitores',          'Monitores gaming y profesionales'),
    ('Cables y Adaptadores','cables-adaptadores','Cables de video, USB, SATA y adaptadores'),
    ('Redes',              'redes',              'Routers, switches y placas de red'),
    ('Pendrives y Memorias','pendrives-memorias', 'Pendrives, tarjetas SD y discos externos'),
    ('Gabinetes',          'gabinetes',          'Gabinetes ATX, Micro-ATX y Mini-ITX'),
    ('Servicios',          'servicios',          'Limpieza, formateo, armado y diagnóstico');

-- ------------------------------------------------------------
-- Tabla: subcategorias
-- ------------------------------------------------------------
CREATE TABLE subcategorias (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(80)  NOT NULL,
    slug         VARCHAR(80)  NOT NULL UNIQUE,
    categoria_id INT          NOT NULL,
    descripcion  VARCHAR(255) DEFAULT '',
    CONSTRAINT fk_sub_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

INSERT INTO subcategorias (nombre, slug, categoria_id) VALUES
    -- Componentes (id=1)
    ('CPU',          'cpu',          1),
    ('GPU',          'gpu',          1),
    ('RAM',          'ram',          1),
    ('SSD',          'ssd',          1),
    ('HDD',          'hdd',          1),
    ('NVMe',         'nvme',         1),
    ('Placas madre', 'placas-madre', 1),
    ('Fuentes de poder','fuentes',   1),
    -- Periféricos (id=2)
    ('Teclados',     'teclados',     2),
    ('Mouse',        'mouse',        2),
    ('Auriculares',  'auriculares',  2),
    ('Webcam',       'webcam',       2),
    ('Pad mouse',    'pad-mouse',    2),
    -- Monitores (id=3)
    ('Gaming',       'monitores-gaming',       3),
    ('Profesional',  'monitores-profesional',  3),
    ('Ultrawide',    'monitores-ultrawide',    3),
    -- Cables y Adaptadores (id=4)
    ('Cables de video','cables-video',   4),
    ('Cables USB',     'cables-usb',     4),
    ('Cables SATA',    'cables-sata',    4),
    ('Adaptadores',    'adaptadores',    4),
    -- Redes (id=5)
    ('Routers',        'routers',        5),
    ('Switches',       'switches',       5),
    ('Placas de red',  'placas-red',     5),
    ('Cables de red',  'cables-red',     5),
    -- Pendrives y Memorias (id=6)
    ('Pendrives',      'pendrives',      6),
    ('Tarjetas SD',    'tarjetas-sd',    6),
    ('Discos externos','discos-externos',6),
    -- Gabinetes (id=7)
    ('ATX',            'atx',            7),
    ('Micro-ATX',      'micro-atx',      7),
    ('Mini-ITX',       'mini-itx',       7),
    -- Servicios (id=8)
    ('Limpieza',       'limpieza',       8),
    ('Formateo',       'formateo',       8),
    ('Armado de PC',   'armado-pc',      8),
    ('Diagnóstico',    'diagnostico',    8);

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120)  NOT NULL,
    descripcion     TEXT          NOT NULL,
    precio          DECIMAL(10,2) NOT NULL,
    stock           INT           NOT NULL DEFAULT 0,
    imagen          VARCHAR(255)  DEFAULT 'default.webp',
    subcategoria_id INT           NOT NULL,
    destacado       TINYINT(1)    NOT NULL DEFAULT 0,
    creado_en       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_subcategoria FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id)
);

INSERT INTO productos (nombre, descripcion, precio, stock, subcategoria_id, destacado) VALUES
    ('Intel Core i7-13700K',    'Procesador 16 núcleos para gaming y productividad', 320.00, 15, 1, 1),
    ('AMD Ryzen 5 7600X',       'Procesador AM5 ideal para gaming 1080p/1440p',      220.00, 20, 1, 0),
    ('NVIDIA RTX 4070',         'GPU para gaming 1440p con DLSS 3',                  580.00, 8,  2, 1),
    ('Kingston FURY 16GB DDR5', 'Kit 2x8GB DDR5 4800MHz',                            85.00,  30, 3, 0),
    ('Samsung 990 Pro 1TB SSD', 'SSD SATA hasta 560MB/s lectura',                    90.00,  25, 4, 0),
    ('WD Blue 2TB HDD',         'Disco duro 7200RPM para almacenamiento masivo',      65.00,  20, 5, 0),
    ('Samsung 990 Pro 1TB NVMe','NVMe PCIe 4.0 hasta 7450MB/s lectura',             120.00,  18, 6, 1),
    ('ASUS ROG Strix B650-E',   'Placa madre AM5 con WiFi 6E',                      280.00,  10, 7, 0),
    ('Corsair RM750x',          'Fuente 750W 80+ Gold modular',                      110.00,  12, 8, 0),
    ('Logitech G Pro X TKL',    'Teclado mecánico gaming tenkeyless',                130.00,  18, 9, 1),
    ('Razer DeathAdder V3',     'Mouse óptico 30000 DPI ultraligero',                 70.00,  22, 10, 0),
    ('LG 27GP850-B 27"',        'Monitor IPS 165Hz QHD para gaming',                280.00,  10, 14, 1),
    ('NZXT H510 Flow',          'Gabinete ATX mid-tower flujo de aire optimizado',    90.00,  12, 28, 0),
    ('Cable HDMI 2.1 2m',       'Cable HDMI 4K 120Hz para gaming',                   15.00,  50, 17, 0),
    ('Limpieza de PC',          'Limpieza completa interna y externa del equipo',     25.00, 999, 31, 0);

-- ------------------------------------------------------------
-- Tabla: ordenes
-- ------------------------------------------------------------
CREATE TABLE ordenes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    token           VARCHAR(16)   NOT NULL UNIQUE,
    usuario_id      INT           NOT NULL,
    estado          ENUM('pendiente','pagado','cancelado') NOT NULL DEFAULT 'pendiente',
    total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    nombre_pago     VARCHAR(100)  DEFAULT '',
    numero_tarjeta  VARCHAR(20)   DEFAULT '',
    creado_en       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orden_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ------------------------------------------------------------
-- Tabla: orden_items
-- ------------------------------------------------------------
CREATE TABLE orden_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    orden_id        INT           NOT NULL,
    producto_id     INT           NOT NULL,
    cantidad        INT           NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_item_orden    FOREIGN KEY (orden_id)    REFERENCES ordenes(id),
    CONSTRAINT fk_item_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- ------------------------------------------------------------
-- Usuario admin por defecto  (password: admin123)
-- ------------------------------------------------------------
INSERT INTO usuarios (nombre, apellido, email, password, confirmado, admin) VALUES
    ('Admin', 'Tienda', 'admin@tienda.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);
