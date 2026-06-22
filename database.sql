-- ============================================================
-- Tienda de Hardware — Script de base de datos
-- ============================================================

DROP DATABASE IF EXISTS tienda_hardware;
CREATE DATABASE tienda_hardware CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_hardware;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(60)  NOT NULL,
    apellido  VARCHAR(60)  NOT NULL,
    email     VARCHAR(100) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    admin     TINYINT(1)   NOT NULL DEFAULT 0
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
    ('Componentes',         'componentes',        'CPU, GPU, RAM, almacenamiento y más'),
    ('Periféricos',         'perifericos',        'Teclados, mouse, auriculares y más'),
    ('Monitores',           'monitores',          'Monitores gaming y profesionales'),
    ('Cables y Adaptadores','cables-adaptadores', 'Cables de video, USB, SATA y adaptadores'),
    ('Redes',               'redes',              'Routers, switches y placas de red'),
    ('Pendrives y Memorias','pendrives-memorias',  'Pendrives, tarjetas SD y discos externos'),
    ('Gabinetes',           'gabinetes',          'Gabinetes ATX, Micro-ATX y Mini-ITX'),
    ('Servicios',           'servicios',          'Limpieza, formateo, armado y diagnóstico');

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
    ('CPU',             'cpu',          1),
    ('GPU',             'gpu',          1),
    ('RAM',             'ram',          1),
    ('SSD',             'ssd',          1),
    ('HDD',             'hdd',          1),
    ('NVMe',            'nvme',         1),
    ('Placas madre',    'placas-madre', 1),
    ('Fuentes de poder','fuentes',      1),
    -- Periféricos (id=2)
    ('Teclados',  'teclados',   2),
    ('Mouse',     'mouse',      2),
    ('Auriculares','auriculares',2),
    ('Webcam',    'webcam',     2),
    ('Pad mouse', 'pad-mouse',  2),
    -- Monitores (id=3)
    ('Gaming',      'monitores-gaming',      3),
    ('Profesional', 'monitores-profesional', 3),
    ('Ultrawide',   'monitores-ultrawide',   3),
    -- Cables y Adaptadores (id=4)
    ('Cables de video','cables-video', 4),
    ('Cables USB',     'cables-usb',   4),
    ('Cables SATA',    'cables-sata',  4),
    ('Adaptadores',    'adaptadores',  4),
    -- Redes (id=5)
    ('Routers',      'routers',   5),
    ('Switches',     'switches',  5),
    ('Placas de red','placas-red',5),
    ('Cables de red','cables-red',5),
    -- Pendrives y Memorias (id=6)
    ('Pendrives',      'pendrives',       6),
    ('Tarjetas SD',    'tarjetas-sd',     6),
    ('Discos externos','discos-externos', 6),
    -- Gabinetes (id=7)
    ('ATX',      'atx',      7),
    ('Micro-ATX','micro-atx',7),
    ('Mini-ITX', 'mini-itx', 7),
    -- Servicios (id=8)
    ('Limpieza',    'limpieza',   8),
    ('Formateo',    'formateo',   8),
    ('Armado de PC','armado-pc',  8),
    ('Diagnóstico', 'diagnostico',8);

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(120)  NOT NULL,
    marca           VARCHAR(60)   NOT NULL DEFAULT '',
    descripcion     TEXT          NOT NULL,
    precio          DECIMAL(10,2) NOT NULL,
    stock           INT           NOT NULL DEFAULT 0,
    imagen          VARCHAR(255)  DEFAULT 'default.webp',
    subcategoria_id INT           NOT NULL,
    destacado       TINYINT(1)    NOT NULL DEFAULT 0,
    CONSTRAINT fk_producto_subcategoria FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id)
);

INSERT INTO productos (nombre, marca, descripcion, precio, stock, imagen, subcategoria_id, destacado) VALUES
    -- CPU (subcategoria 1)
    ('Intel Core i7-13700K',       'Intel',    'Procesador 16 núcleos para gaming y productividad',    320.00, 15, 'proceI7.webp',       1, 1),
    ('AMD Ryzen 5 7600X',          'AMD',      'Procesador AM5 ideal para gaming 1080p/1440p',         220.00, 20, 'proceRyzen5.jpg',    1, 0),
    ('AMD Ryzen 9 7950X',          'AMD',      '16 núcleos y 32 hilos para workstation y streaming',   550.00,  6, 'proceRyzen9.jpg',    1, 0),
    ('Intel Core i5-13400F',       'Intel',    '10 núcleos, excelente relación precio/rendimiento',    195.00, 18, 'proceI5.webp',       1, 0),
    -- GPU (subcategoria 2)
    ('NVIDIA RTX 4070',            'NVIDIA',   'GPU para gaming 1440p con DLSS 3',                     580.00,  8, 'grafica4070.webp',   2, 1),
    ('ASUS TUF RTX 4060 OC',       'ASUS',     'RTX 4060 con disipación TUF, ideal 1080p ultra',       330.00, 10, 'grafica4060.jpg',    2, 0),
    ('Gigabyte Radeon RX 7600',    'Gigabyte', 'GPU RDNA3 para gaming 1080p de alto framerate',        280.00,  9, 'grafica7600.webp',   2, 0),
    -- RAM (subcategoria 3)
    ('Kingston FURY 16GB DDR5',    'Kingston', 'Kit 2x8GB DDR5 4800MHz',                                85.00, 30, 'ramHyperx.jpg',      3, 0),
    ('Corsair Vengeance 32GB DDR5','Corsair',  'Kit 2x16GB DDR5 5600MHz CL36',                         140.00, 14, 'ramCorsair.webp',    3, 0),
    ('ADATA XPG Gammix 16GB DDR4', 'ADATA',    'Kit 2x8GB DDR4 3200MHz con disipador',                  55.00, 25, 'ramXpg.jpg',         3, 0),
    -- SSD (subcategoria 4)
    ('Samsung 870 EVO 1TB SSD',    'Samsung',  'SSD SATA hasta 560MB/s lectura',                        90.00, 25, 'ssdSamsung.webp',    4, 0),
    ('Crucial MX500 1TB SSD',      'Crucial',  'SSD SATA confiable con caché dinámica',                 75.00, 22, 'ssdCrucial.webp',    4, 0),
    -- HDD (subcategoria 5)
    ('WD Blue 2TB HDD',            'WD',       'Disco duro 7200RPM para almacenamiento masivo',         65.00, 20, 'hddBlue.webp',       5, 0),
    ('Seagate Barracuda 4TB HDD',  'Seagate',  'Disco 5400RPM ideal para backups y media',              95.00, 12, 'hddSeagate.jpg',     5, 0),
    -- NVMe (subcategoria 6)
    ('Samsung 990 Pro 1TB NVMe',   'Samsung',  'NVMe PCIe 4.0 hasta 7450MB/s lectura',                 120.00, 18, 'nvmeSamsung.webp',   6, 1),
    ('WD Black SN850X 1TB NVMe',   'WD',       'NVMe PCIe 4.0 para gaming, 7300MB/s',                  115.00, 16, 'nvmeBlack.webp',     6, 0),
    -- Placas madre (subcategoria 7)
    ('ASUS ROG Strix B650-E',      'ASUS',     'Placa madre AM5 con WiFi 6E',                          280.00, 10, 'motherROG.webp',     7, 0),
    ('MSI B550 Tomahawk',          'MSI',      'Placa AM4 con VRM robusto y PCIe 4.0',                 150.00, 11, 'motherMSI.jpg',      7, 0),
    -- Fuentes (subcategoria 8)
    ('Corsair RM750x',             'Corsair',  'Fuente 750W 80+ Gold modular',                         110.00, 12, 'fuenteCorsair.jpg',  8, 0),
    ('EVGA 600 BR',                'EVGA',     'Fuente 600W 80+ Bronze',                                55.00, 15, 'fuenteEVGA.webp',    8, 0),
    -- Teclados (subcategoria 9)
    ('Logitech G Pro X TKL',       'Logitech', 'Teclado mecánico gaming tenkeyless',                   130.00, 18, 'tecladoLogitech.webp',  9, 1),
    ('Redragon Kumara K552',       'Redragon', 'Mecánico compacto switches red, RGB',                   45.00, 28, 'tecladoReddragon.webp', 9, 0),
    ('HyperX Alloy Origins',       'HyperX',   'Teclado mecánico switches HyperX Red',                  90.00, 13, 'tecladoHyperx.webp',    9, 0),
    -- Mouse (subcategoria 10)
    ('Razer DeathAdder V3',        'Razer',    'Mouse óptico 30000 DPI ultraligero',                    70.00, 22, 'mouseRazer.jpg',     10, 0),
    ('Logitech G502 HERO',         'Logitech', 'Sensor HERO 25K, 11 botones programables',              60.00, 24, 'mouseLogitech.webp', 10, 0),
    -- Auriculares (subcategoria 11)
    ('HyperX Cloud II',            'HyperX',   'Auriculares 7.1 con micrófono desmontable',             95.00, 17, 'auriHyperx.jpg',     11, 0),
    -- Webcam (subcategoria 12)
    ('Logitech C920 HD Pro',       'Logitech', 'Webcam Full HD 1080p con doble micrófono',              80.00, 14, 'webLogitech.webp',   12, 0),
    -- Pad mouse (subcategoria 13)
    ('Pad gamer XL 80x30',         'Redragon', 'Superficie de tela speed con bordes cosidos',           18.00, 35, 'pad.webp',           13, 0),
    -- Monitores gaming (subcategoria 14)
    ('LG 27GP850-B 27"',           'LG',       'Monitor IPS 165Hz QHD para gaming',                    280.00, 10, 'lg27.webp',          14, 1),
    ('Samsung Odyssey G5 32"',     'Samsung',  'Curvo VA 144Hz QHD 1ms',                               330.00,  7, 'samsung.png',        14, 0),
    -- Monitores profesional (subcategoria 15)
    ('Dell UltraSharp U2723QE',    'Dell',     'IPS Black 4K, 98% DCI-P3 para diseño',                 520.00,  5, 'Dell.webp',          15, 0),
    -- Ultrawide (subcategoria 16)
    ('LG 34WP65C 34" Ultrawide',   'LG',       'Curvo 21:9 QHD 160Hz HDR10',                           380.00,  6, 'lg34.webp',          16, 0),
    -- Cables de video (subcategoria 17)
    ('Cable HDMI 2.1 2m',          'UGREEN',   'Cable HDMI 4K 120Hz para gaming',                       15.00, 50, 'hdmi.webp',   17, 0),
    ('Cable DisplayPort 1.4 2m',   'Vention',  'DP 1.4 8K@60Hz / 4K@144Hz',                             18.00, 40, 'displayport.webp',   17, 0),
    -- Cables USB (subcategoria 18)
    ('Cable USB-C 100W 1m',        'UGREEN',   'Carga rápida PD 100W y datos 480Mbps',                  10.00, 60, 'usb-c.webp',         18, 0),
    -- Cables SATA (subcategoria 19)
    ('Cable SATA III 50cm x2',     'Genérico', 'Par de cables SATA 6Gb/s con traba metálica',            6.00, 80, 'sata.webp',          19, 0),
    -- Adaptadores (subcategoria 20)
    ('Adaptador USB-C a HDMI',     'UGREEN',   '4K@60Hz, compatible con modo DP Alt',                   22.00, 30, 'adaptador.jpg',      20, 0),
    -- Routers (subcategoria 21)
    ('TP-Link Archer AX55',        'TP-Link',  'Router WiFi 6 AX3000 doble banda',                     110.00, 12, 'router.webp',        21, 0),
    -- Switches (subcategoria 22)
    ('TP-Link LS108G 8 puertos',   'TP-Link',  'Switch gigabit de escritorio no administrable',         30.00, 20, 'switch.jpg',         22, 0),
    -- Placas de red (subcategoria 23)
    ('Placa WiFi 6 PCIe AX200',    'Intel',    'WiFi 6 + Bluetooth 5.2 con antenas externas',           35.00, 16, 'placaRed.webp',      23, 0),
    -- Cables de red (subcategoria 24)
    ('Cable de red Cat6 5m',       'Genérico', 'Patch cord UTP Cat6 certificado',                        8.00, 70, 'cableEthernet.jpg',  24, 0),
    -- Pendrives (subcategoria 25)
    ('Pendrive Kingston 64GB',     'Kingston', 'USB 3.2 DataTraveler Exodia',                           12.00, 55, 'pendrive.jpg',       25, 0),
    -- Tarjetas SD (subcategoria 26)
    ('SanDisk Ultra microSD 128GB','SanDisk',  'Clase 10 A1 con adaptador SD',                          20.00, 45, 'micro.webp',         26, 0),
    -- Discos externos (subcategoria 27)
    ('Seagate Expansion 2TB',      'Seagate',  'Disco externo USB 3.0 portátil',                        85.00, 15, 'externo.webp',       27, 0),
    -- Gabinetes ATX (subcategoria 28)
    ('NZXT H510 Flow',             'NZXT',         'Gabinete ATX mid-tower flujo de aire optimizado',   90.00, 12, 'nzxt.webp',          28, 0),
    ('Corsair 4000D Airflow',      'Corsair',      'ATX con frente mesh y excelente cable management', 105.00,  9, 'corsair.jpg',        28, 1),
    -- Mini-ITX (subcategoria 30)
    ('Cooler Master NR200',        'Cooler Master','Mini-ITX compacto con gran compatibilidad',         95.00,  8, 'coolmaster.webp',    30, 0),
    -- Servicios (subcategorias 31-34)
    ('Limpieza de PC',             '', 'Limpieza completa interna y externa del equipo',  25.00, 999, 'default.webp', 31, 0),
    ('Formateo e instalación',     '', 'Formateo, sistema operativo y drivers al día',    20.00, 999, 'default.webp', 32, 0),
    ('Armado de PC',               '', 'Armado profesional con gestión de cables',        40.00, 999, 'default.webp', 33, 0),
    ('Diagnóstico de equipo',      '', 'Detección de fallas con informe detallado',       15.00, 999, 'default.webp', 34, 0);

-- ------------------------------------------------------------
-- Tabla: ordenes
-- ------------------------------------------------------------
CREATE TABLE ordenes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    token          VARCHAR(16)   NOT NULL UNIQUE,
    usuario_id     INT           NOT NULL,
    estado         ENUM('pendiente','pagado','cancelado') NOT NULL DEFAULT 'pendiente',
    total          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    nombre_pago    VARCHAR(100)  DEFAULT '',
    numero_tarjeta VARCHAR(20)   DEFAULT '',
    creado_en      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
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
-- Usuario admin por defecto (password: admin123)
-- ------------------------------------------------------------
INSERT INTO usuarios (nombre, apellido, email, password, admin) VALUES
    ('Admin', 'Tienda', 'admin@tienda.com',
     '$2y$10$8cQZ8am/g6M1vCHHv6F4n.olGh9zTPERLy.nfWvHxq9Nr9ISeFqJ6', 1);