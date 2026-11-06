# SIAR – Mapa de pruebas

Diagramas de los casos de prueba por módulo (camino feliz y flujos alternos). Verde = éxito esperado, rojo = rechazo esperado, gris punteado = pendiente.

## Organización de la suite

```mermaid
flowchart TB
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef carpeta fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef existe fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef nueva fill:#fff4d6,stroke:#b8860b,color:#3a2a00,stroke-dasharray:4 3
  S["`**php artisan test**
cobertura mínima 70 %`"]:::raiz
  U["`**tests/Unit**
lógica pura, sin HTTP`"]:::carpeta
  F["`**tests/Feature**
HTTP + base de datos`"]:::carpeta
  S --> U
  S --> F
  U --> U1["`EstadoPedidoTest
transiciones y permisos`"]:::nueva
  U --> U2["`DescuentoStrategyTest
porcentaje, valor fijo, factory`"]:::nueva
  U --> U3["`InventarioServiceTest
stock y Agotado`"]:::nueva
  U --> U4["`FacturacionServiceTest
impuesto, total, consecutivo`"]:::nueva
  F --> F1["`**RbacTest**
permisos por rol`"]:::existe
  F --> F2["`**FlujoRestauranteTest**
pedido → cocina → factura`"]:::existe
  F --> F3["`**ModeloDatosTest**
relaciones y soft deletes`"]:::existe
  F --> F4["`**Auth/* y ProfileTest**
Breeze: login, registro, perfil`"]:::existe
  F --> F5["`ReporteTest
RF028–RF032`"]:::nueva
```

## Flujo completo: pedido → cocina → factura

```mermaid
sequenceDiagram
  autonumber
  actor M as Mesero
  actor K as Cocinero
  actor J as Cajero
  participant S as SIAR
  participant D as Base de datos
  M->>S: Crear pedido en mesa 1 con 2 productos
  S->>D: Guarda pedido y detalle, descuenta stock
  Note over S,D: FLU-01 · estado Pendiente, mesa Ocupada, subtotal correcto
  M->>S: Enviar a cocina
  Note over S: FLU-02 · estado En preparación, visible en cocina
  K->>S: Marcar Listo
  Note over S: FLU-03 · estado Listo
  M->>S: Marcar Entregado
  Note over S: FLU-04 · estado Entregado
  J->>S: Generar factura con descuento del 10 por ciento
  S->>D: Guarda factura
  Note over S,D: FLU-05 · número FAC-000001, impuesto y total correctos
  J->>S: Registrar pago en efectivo
  S->>D: Guarda pago, cierra pedido
  Note over S,D: FLU-06 · pedido Facturado, cambio calculado, mesa liberada
  S->>D: Auditoría de cada paso
  Note over S,D: FLU-07 · un registro por acción con usuario y fecha
```

## Usuarios y autenticación (RF001–RF006)

Clases: Auth/RegistrationTest · Auth/AuthenticationTest · Auth/PasswordResetTest · ProfileTest · RbacTest

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Usuarios y autenticación**
RF001–RF006`"]:::raiz
  A1["`**Registrar usuario**`"]:::accion
  R --> A1
  A2["`**Iniciar sesión**`"]:::accion
  R --> A2
  A3["`**Recuperar contraseña**`"]:::accion
  R --> A3
  A4["`**Editar perfil**`"]:::accion
  R --> A4
  A5["`**Eliminar cuenta propia**`"]:::accion
  R --> A5
  A6["`**Administrar usuarios y roles**`"]:::accion
  R --> A6
  A1 -->|"`Correo válido y contraseña confirmada`"| C1["`**USR-01** · Funcional
Usuario creado con contraseña cifrada`"]:::ok
  A1 -->|"`Correo con formato inválido`"| C2["`**USR-02** · Funcional
Error de validación en el correo`"]:::err
  A1 -->|"`Correo ya registrado`"| C3["`**USR-03** · Funcional
Error: el correo ya existe`"]:::err
  A1 -->|"`Confirmación distinta`"| C4["`**USR-04** · Funcional
Error en la contraseña`"]:::err
  A2 -->|"`Credenciales correctas y usuario activo`"| C5["`**USR-05** · Funcional
Autenticado y redirigido al dashboard`"]:::ok
  A2 -->|"`Contraseña incorrecta`"| C6["`**USR-06** · Funcional
Sigue como invitado con mensaje de error`"]:::err
  A2 -->|"`Usuario inactivo`"| C7["`**USR-07** · Integración
Acceso rechazado por attemptWhen`"]:::err
  A2 -->|"`Usuario desactivado con sesión abierta`"| C8["`**USR-08** · Integración
EnsureUserIsActive cierra la sesión`"]:::err
  A2 -->|"`Login y logout`"| C9["`**USR-09** · Integración
Ambos quedan en la auditoría`"]:::ok
  A3 -->|"`Correo registrado`"| C10["`**USR-10** · Funcional
Se envía el enlace de restablecimiento`"]:::ok
  A3 -->|"`Token válido`"| C11["`**USR-11** · Funcional
Contraseña nueva guardada cifrada`"]:::ok
  A3 -->|"`Token inválido o vencido`"| C12["`**USR-12** · Funcional
Error y la contraseña no cambia`"]:::err
  A4 -->|"`Datos válidos`"| C13["`**USR-13** · Funcional
Perfil actualizado y auditado`"]:::ok
  A4 -->|"`Correo de otro usuario`"| C14["`**USR-14** · Funcional
Error de correo duplicado`"]:::err
  A5 -->|"`Contraseña correcta`"| C15["`**USR-15** · Funcional
Cuenta eliminada y sesión cerrada`"]:::ok
  A5 -->|"`Contraseña incorrecta`"| C16["`**USR-16** · Funcional
La cuenta se conserva`"]:::err
  A6 -->|"`Admin elimina Cajero, Mesero o Cocinero`"| C17["`**USR-17** · Integración
Cuenta eliminada y auditada`"]:::ok
  A6 -->|"`Admin intenta eliminar a otro Administrador`"| C18["`**USR-18** · Integración
Respuesta 403`"]:::err
  A6 -->|"`Mesero intenta eliminar un usuario`"| C19["`**USR-19** · Integración
Respuesta 403`"]:::err
  A6 -->|"`Admin asigna un rol`"| C20["`**USR-20** · Integración
hasRole verdadero y cambio auditado`"]:::ok
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| USR-01 | Registrar usuario | Correo válido y contraseña confirmada | Usuario creado con contraseña cifrada | Funcional |
| USR-02 | Registrar usuario | Correo con formato inválido | Error de validación en el correo | Funcional |
| USR-03 | Registrar usuario | Correo ya registrado | Error: el correo ya existe | Funcional |
| USR-04 | Registrar usuario | Confirmación distinta | Error en la contraseña | Funcional |
| USR-05 | Iniciar sesión | Credenciales correctas y usuario activo | Autenticado y redirigido al dashboard | Funcional |
| USR-06 | Iniciar sesión | Contraseña incorrecta | Sigue como invitado con mensaje de error | Funcional |
| USR-07 | Iniciar sesión | Usuario inactivo | Acceso rechazado por attemptWhen | Integración |
| USR-08 | Iniciar sesión | Usuario desactivado con sesión abierta | EnsureUserIsActive cierra la sesión | Integración |
| USR-09 | Iniciar sesión | Login y logout | Ambos quedan en la auditoría | Integración |
| USR-10 | Recuperar contraseña | Correo registrado | Se envía el enlace de restablecimiento | Funcional |
| USR-11 | Recuperar contraseña | Token válido | Contraseña nueva guardada cifrada | Funcional |
| USR-12 | Recuperar contraseña | Token inválido o vencido | Error y la contraseña no cambia | Funcional |
| USR-13 | Editar perfil | Datos válidos | Perfil actualizado y auditado | Funcional |
| USR-14 | Editar perfil | Correo de otro usuario | Error de correo duplicado | Funcional |
| USR-15 | Eliminar cuenta propia | Contraseña correcta | Cuenta eliminada y sesión cerrada | Funcional |
| USR-16 | Eliminar cuenta propia | Contraseña incorrecta | La cuenta se conserva | Funcional |
| USR-17 | Administrar usuarios y roles | Admin elimina Cajero, Mesero o Cocinero | Cuenta eliminada y auditada | Integración |
| USR-18 | Administrar usuarios y roles | Admin intenta eliminar a otro Administrador | Respuesta 403 | Integración |
| USR-19 | Administrar usuarios y roles | Mesero intenta eliminar un usuario | Respuesta 403 | Integración |
| USR-20 | Administrar usuarios y roles | Admin asigna un rol | hasRole verdadero y cambio auditado | Integración |

## Menú y categorías (RF007–RF011)

Clases: FlujoRestauranteTest · ModeloDatosTest · ProductoTest (nueva)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Menú y categorías**
RF007–RF011`"]:::raiz
  A1["`**Crear producto**`"]:::accion
  R --> A1
  A2["`**Modificar producto**`"]:::accion
  R --> A2
  A3["`**Eliminar producto**`"]:::accion
  R --> A3
  A4["`**Categorías**`"]:::accion
  R --> A4
  A5["`**Disponibilidad manual**`"]:::accion
  R --> A5
  A6["`**Búsqueda**`"]:::accion
  R --> A6
  A1 -->|"`Datos completos con imagen JPG`"| C1["`**MEN-01** · Funcional
Producto creado e imagen en storage`"]:::ok
  A1 -->|"`Sin nombre o sin precio`"| C2["`**MEN-02** · Funcional
Error de validación`"]:::err
  A1 -->|"`Precio negativo`"| C3["`**MEN-03** · Funcional
Error de validación`"]:::err
  A1 -->|"`Archivo que no es imagen`"| C4["`**MEN-04** · Funcional
Error de validación en la imagen`"]:::err
  A2 -->|"`Datos válidos con imagen nueva`"| C5["`**MEN-05** · Funcional
Producto actualizado, imagen anterior reemplazada`"]:::ok
  A3 -->|"`Producto con pedidos anteriores`"| C6["`**MEN-06** · Integración
Soft delete, el detalle del pedido se conserva`"]:::ok
  A3 -->|"`Producto eliminado`"| C7["`**MEN-07** · Funcional
No aparece en el menú ni al crear pedido`"]:::ok
  A4 -->|"`Nombre único`"| C8["`**MEN-08** · Funcional
Categoría creada`"]:::ok
  A4 -->|"`Nombre vacío o repetido`"| C9["`**MEN-09** · Funcional
Error de validación`"]:::err
  A4 -->|"`Eliminar categoría con productos`"| C10["`**MEN-10** · Integración
Productos quedan sin categoría`"]:::ok
  A5 -->|"`Cocinero marca Agotado`"| C11["`**MEN-11** · Funcional
Estado del producto cambia`"]:::ok
  A5 -->|"`Mesero intenta cambiarla`"| C12["`**MEN-12** · Integración
Respuesta 403`"]:::err
  A6 -->|"`Texto más filtro de categoría`"| C13["`**MEN-13** · Unitaria
Solo productos de esa categoría`"]:::ok
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| MEN-01 | Crear producto | Datos completos con imagen JPG | Producto creado e imagen en storage | Funcional |
| MEN-02 | Crear producto | Sin nombre o sin precio | Error de validación | Funcional |
| MEN-03 | Crear producto | Precio negativo | Error de validación | Funcional |
| MEN-04 | Crear producto | Archivo que no es imagen | Error de validación en la imagen | Funcional |
| MEN-05 | Modificar producto | Datos válidos con imagen nueva | Producto actualizado, imagen anterior reemplazada | Funcional |
| MEN-06 | Eliminar producto | Producto con pedidos anteriores | Soft delete, el detalle del pedido se conserva | Integración |
| MEN-07 | Eliminar producto | Producto eliminado | No aparece en el menú ni al crear pedido | Funcional |
| MEN-08 | Categorías | Nombre único | Categoría creada | Funcional |
| MEN-09 | Categorías | Nombre vacío o repetido | Error de validación | Funcional |
| MEN-10 | Categorías | Eliminar categoría con productos | Productos quedan sin categoría | Integración |
| MEN-11 | Disponibilidad manual | Cocinero marca Agotado | Estado del producto cambia | Funcional |
| MEN-12 | Disponibilidad manual | Mesero intenta cambiarla | Respuesta 403 | Integración |
| MEN-13 | Búsqueda | Texto más filtro de categoría | Solo productos de esa categoría | Unitaria |

## Mesas y reservas (RF012–RF016)

Clases: FlujoRestauranteTest · RbacTest · ReservaServiceTest (nueva)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Mesas y reservas**
RF012–RF016`"]:::raiz
  A1["`**Crear mesa**`"]:::accion
  R --> A1
  A2["`**Modificar mesa**`"]:::accion
  R --> A2
  A3["`**Consultar estado**`"]:::accion
  R --> A3
  A4["`**Reservar mesa**`"]:::accion
  R --> A4
  A5["`**Liberar mesa**`"]:::accion
  R --> A5
  A1 -->|"`Identificador único y capacidad`"| C1["`**MES-01** · Funcional
Mesa creada en estado Disponible`"]:::ok
  A1 -->|"`Identificador repetido`"| C2["`**MES-02** · Funcional
Error de identificador duplicado`"]:::err
  A1 -->|"`Capacidad vacía o cero`"| C3["`**MES-03** · Funcional
Error de validación`"]:::err
  A1 -->|"`Mesero intenta crearla`"| C4["`**MES-04** · Integración
Respuesta 403`"]:::err
  A2 -->|"`Datos válidos`"| C5["`**MES-05** · Funcional
Mesa actualizada y auditada`"]:::ok
  A3 -->|"`Filtro por estado`"| C6["`**MES-06** · Funcional
Solo mesas de ese estado`"]:::ok
  A3 -->|"`Sin mesas creadas`"| C7["`**MES-07** · Funcional
Mensaje informativo`"]:::ok
  A4 -->|"`Mesa Disponible y fecha futura`"| C8["`**MES-08** · Integración
Mesa Reservada y reserva registrada`"]:::ok
  A4 -->|"`Mesa Ocupada o Reservada`"| C9["`**MES-09** · Unitaria
ReglaNegocioException`"]:::err
  A4 -->|"`Fecha u hora pasada`"| C10["`**MES-10** · Funcional
Error de validación`"]:::err
  A5 -->|"`Mesa Ocupada o Reservada`"| C11["`**MES-11** · Integración
Mesa Disponible y auditada`"]:::ok
  A5 -->|"`Mesa ya Disponible`"| C12["`**MES-12** · Unitaria
Sin cambios y mensaje de error`"]:::err
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| MES-01 | Crear mesa | Identificador único y capacidad | Mesa creada en estado Disponible | Funcional |
| MES-02 | Crear mesa | Identificador repetido | Error de identificador duplicado | Funcional |
| MES-03 | Crear mesa | Capacidad vacía o cero | Error de validación | Funcional |
| MES-04 | Crear mesa | Mesero intenta crearla | Respuesta 403 | Integración |
| MES-05 | Modificar mesa | Datos válidos | Mesa actualizada y auditada | Funcional |
| MES-06 | Consultar estado | Filtro por estado | Solo mesas de ese estado | Funcional |
| MES-07 | Consultar estado | Sin mesas creadas | Mensaje informativo | Funcional |
| MES-08 | Reservar mesa | Mesa Disponible y fecha futura | Mesa Reservada y reserva registrada | Integración |
| MES-09 | Reservar mesa | Mesa Ocupada o Reservada | ReglaNegocioException | Unitaria |
| MES-10 | Reservar mesa | Fecha u hora pasada | Error de validación | Funcional |
| MES-11 | Liberar mesa | Mesa Ocupada o Reservada | Mesa Disponible y auditada | Integración |
| MES-12 | Liberar mesa | Mesa ya Disponible | Sin cambios y mensaje de error | Unitaria |

## Pedidos (RF017–RF022)

Clases: FlujoRestauranteTest · PedidoServiceTest (nueva)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Pedidos**
RF017–RF022`"]:::raiz
  A1["`**Crear pedido**`"]:::accion
  R --> A1
  A2["`**Agregar productos**`"]:::accion
  R --> A2
  A3["`**Modificar pedido**`"]:::accion
  R --> A3
  A4["`**Cancelar pedido**`"]:::accion
  R --> A4
  A5["`**Cocina y seguimiento**`"]:::accion
  R --> A5
  A1 -->|"`Productos disponibles y mesa`"| C1["`**PED-01** · Integración
Pedido Pendiente, subtotal = Σ cantidad × precio`"]:::ok
  A1 -->|"`Pedido guardado`"| C2["`**PED-02** · Integración
La mesa pasa a Ocupada`"]:::ok
  A1 -->|"`Sin productos`"| C3["`**PED-03** · Funcional
Error: el pedido está vacío`"]:::err
  A1 -->|"`Sin mesa`"| C4["`**PED-04** · Funcional
Error: se requiere mesa`"]:::err
  A1 -->|"`Producto Agotado`"| C5["`**PED-05** · Integración
Producto rechazado`"]:::err
  A1 -->|"`Cantidad mayor al stock`"| C6["`**PED-06** · Unitaria
Producto rechazado`"]:::err
  A1 -->|"`Precio del producto cambia después`"| C7["`**PED-07** · Unitaria
El detalle conserva el precio original`"]:::ok
  A2 -->|"`Pendiente, En preparación o Listo`"| C8["`**PED-08** · Integración
Agregado y total recalculado`"]:::ok
  A2 -->|"`Entregado o Facturado`"| C9["`**PED-09** · Unitaria
Rechazado`"]:::err
  A3 -->|"`Estado Pendiente`"| C10["`**PED-10** · Integración
Cantidades cambiadas y total recalculado`"]:::ok
  A3 -->|"`Cualquier otro estado`"| C11["`**PED-11** · Unitaria
Rechazado`"]:::err
  A4 -->|"`Estado Pendiente`"| C12["`**PED-12** · Integración
Pedido Cancelado y auditado`"]:::ok
  A4 -->|"`Cualquier otro estado`"| C13["`**PED-13** · Unitaria
Rechazado`"]:::err
  A5 -->|"`Pedido enviado a cocina`"| C14["`**PED-14** · Funcional
Aparece en la vista de cocina`"]:::ok
  A5 -->|"`Consultar pedido`"| C15["`**PED-15** · Funcional
Muestra estado actual e historial`"]:::ok
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| PED-01 | Crear pedido | Productos disponibles y mesa | Pedido Pendiente, subtotal = Σ cantidad × precio | Integración |
| PED-02 | Crear pedido | Pedido guardado | La mesa pasa a Ocupada | Integración |
| PED-03 | Crear pedido | Sin productos | Error: el pedido está vacío | Funcional |
| PED-04 | Crear pedido | Sin mesa | Error: se requiere mesa | Funcional |
| PED-05 | Crear pedido | Producto Agotado | Producto rechazado | Integración |
| PED-06 | Crear pedido | Cantidad mayor al stock | Producto rechazado | Unitaria |
| PED-07 | Crear pedido | Precio del producto cambia después | El detalle conserva el precio original | Unitaria |
| PED-08 | Agregar productos | Pendiente, En preparación o Listo | Agregado y total recalculado | Integración |
| PED-09 | Agregar productos | Entregado o Facturado | Rechazado | Unitaria |
| PED-10 | Modificar pedido | Estado Pendiente | Cantidades cambiadas y total recalculado | Integración |
| PED-11 | Modificar pedido | Cualquier otro estado | Rechazado | Unitaria |
| PED-12 | Cancelar pedido | Estado Pendiente | Pedido Cancelado y auditado | Integración |
| PED-13 | Cancelar pedido | Cualquier otro estado | Rechazado | Unitaria |
| PED-14 | Cocina y seguimiento | Pedido enviado a cocina | Aparece en la vista de cocina | Funcional |
| PED-15 | Cocina y seguimiento | Consultar pedido | Muestra estado actual e historial | Funcional |

## Inventario (RF011 · Módulo 5 del enunciado)

Clases: InventarioServiceTest (nueva) · ModeloDatosTest

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Inventario**
RF011 · Módulo 5 del enunciado`"]:::raiz
  A1["`**Descontar stock**`"]:::accion
  R --> A1
  A2["`**Disponibilidad automática**`"]:::accion
  R --> A2
  A1 -->|"`Pedido confirmado`"| C1["`**INV-01** · Unitaria
Stock baja según las cantidades`"]:::ok
  A1 -->|"`Descuento mayor al stock`"| C2["`**INV-02** · Unitaria
Rechazado, el stock nunca queda negativo`"]:::err
  A2 -->|"`Stock llega a 0`"| C3["`**INV-03** · Unitaria
Producto pasa a Agotado`"]:::ok
  A2 -->|"`Stock repuesto`"| C4["`**INV-04** · Unitaria
Producto vuelve a Disponible`"]:::ok
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| INV-01 | Descontar stock | Pedido confirmado | Stock baja según las cantidades | Unitaria |
| INV-02 | Descontar stock | Descuento mayor al stock | Rechazado, el stock nunca queda negativo | Unitaria |
| INV-03 | Disponibilidad automática | Stock llega a 0 | Producto pasa a Agotado | Unitaria |
| INV-04 | Disponibilidad automática | Stock repuesto | Producto vuelve a Disponible | Unitaria |

## Facturación (RF023–RF027)

Clases: FlujoRestauranteTest · FacturacionServiceTest (nueva) · DescuentoStrategyTest (nueva)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Facturación**
RF023–RF027`"]:::raiz
  A1["`**Generar factura**`"]:::accion
  R --> A1
  A2["`**Calcular impuesto**`"]:::accion
  R --> A2
  A3["`**Aplicar descuento**`"]:::accion
  R --> A3
  A4["`**Registrar pago**`"]:::accion
  R --> A4
  A5["`**Emitir comprobante**`"]:::accion
  R --> A5
  A1 -->|"`Pedido Entregado`"| C1["`**FAC-01** · Integración
Factura FAC-000001 creada`"]:::ok
  A1 -->|"`Segunda factura`"| C2["`**FAC-02** · Unitaria
Número consecutivo FAC-000002`"]:::ok
  A1 -->|"`Pedido no Entregado`"| C3["`**FAC-03** · Unitaria
Rechazado`"]:::err
  A1 -->|"`Pedido ya facturado`"| C4["`**FAC-04** · Integración
Rechazado, una factura por pedido`"]:::err
  A2 -->|"`Subtotal 50.000 con 8 %`"| C5["`**FAC-05** · Unitaria
Impuesto 4.000 en ítem separado`"]:::ok
  A2 -->|"`SIAR_IMPUESTO_PORCENTAJE = 19`"| C6["`**FAC-06** · Unitaria
Usa el porcentaje configurado`"]:::ok
  A3 -->|"`Porcentaje 10 % sobre 50.000`"| C7["`**FAC-07** · Unitaria
Descuento 5.000`"]:::ok
  A3 -->|"`Valor fijo 8.000`"| C8["`**FAC-08** · Unitaria
Descuento 8.000`"]:::ok
  A3 -->|"`Descuento mayor al subtotal`"| C9["`**FAC-09** · Unitaria
Rechazado`"]:::err
  A3 -->|"`Porcentaje negativo o mayor a 100`"| C10["`**FAC-10** · Unitaria
Rechazado`"]:::err
  A3 -->|"`Tipo desconocido en DescuentoFactory`"| C11["`**FAC-11** · Unitaria
Lanza excepción`"]:::err
  A3 -->|"`Mesero intenta aplicarlo`"| C12["`**FAC-12** · Integración
Respuesta 403`"]:::err
  A4 -->|"`Monto ≥ total`"| C13["`**FAC-13** · Integración
Pago guardado, cambio calculado, pedido Facturado`"]:::ok
  A4 -->|"`Monto menor al total`"| C14["`**FAC-14** · Unitaria
Rechazado`"]:::err
  A4 -->|"`Método de pago no permitido`"| C15["`**FAC-15** · Funcional
Error de validación`"]:::err
  A5 -->|"`Factura pagada`"| C16["`**FAC-16** · Funcional
Comprobante imprimible con detalle e impuestos`"]:::ok
  A5 -->|"`Factura sin pago`"| C17["`**FAC-17** · Funcional
No se permite emitir`"]:::err
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| FAC-01 | Generar factura | Pedido Entregado | Factura FAC-000001 creada | Integración |
| FAC-02 | Generar factura | Segunda factura | Número consecutivo FAC-000002 | Unitaria |
| FAC-03 | Generar factura | Pedido no Entregado | Rechazado | Unitaria |
| FAC-04 | Generar factura | Pedido ya facturado | Rechazado, una factura por pedido | Integración |
| FAC-05 | Calcular impuesto | Subtotal 50.000 con 8 % | Impuesto 4.000 en ítem separado | Unitaria |
| FAC-06 | Calcular impuesto | SIAR_IMPUESTO_PORCENTAJE = 19 | Usa el porcentaje configurado | Unitaria |
| FAC-07 | Aplicar descuento | Porcentaje 10 % sobre 50.000 | Descuento 5.000 | Unitaria |
| FAC-08 | Aplicar descuento | Valor fijo 8.000 | Descuento 8.000 | Unitaria |
| FAC-09 | Aplicar descuento | Descuento mayor al subtotal | Rechazado | Unitaria |
| FAC-10 | Aplicar descuento | Porcentaje negativo o mayor a 100 | Rechazado | Unitaria |
| FAC-11 | Aplicar descuento | Tipo desconocido en DescuentoFactory | Lanza excepción | Unitaria |
| FAC-12 | Aplicar descuento | Mesero intenta aplicarlo | Respuesta 403 | Integración |
| FAC-13 | Registrar pago | Monto ≥ total | Pago guardado, cambio calculado, pedido Facturado | Integración |
| FAC-14 | Registrar pago | Monto menor al total | Rechazado | Unitaria |
| FAC-15 | Registrar pago | Método de pago no permitido | Error de validación | Funcional |
| FAC-16 | Emitir comprobante | Factura pagada | Comprobante imprimible con detalle e impuestos | Funcional |
| FAC-17 | Emitir comprobante | Factura sin pago | No se permite emitir | Funcional |

## Auditoría y seguridad (RNF004–RNF006)

Clases: RbacTest · ModeloDatosTest · AuditoriaObserverTest (nueva)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Auditoría y seguridad**
RNF004–RNF006`"]:::raiz
  A1["`**Observer de auditoría**`"]:::accion
  R --> A1
  A2["`**Acceso a la auditoría**`"]:::accion
  R --> A2
  A3["`**Rutas protegidas**`"]:::accion
  R --> A3
  A1 -->|"`Crear, editar o eliminar un modelo auditado`"| C1["`**AUD-01** · Integración
Registro con usuario, acción y fecha`"]:::ok
  A1 -->|"`Cambio de contraseña`"| C2["`**AUD-02** · Unitaria
La contraseña nunca se guarda en la auditoría`"]:::ok
  A2 -->|"`Administrador`"| C3["`**AUD-03** · Integración
Ve el listado`"]:::ok
  A2 -->|"`Cajero, Mesero o Cocinero`"| C4["`**AUD-04** · Integración
Respuesta 403`"]:::err
  A3 -->|"`Invitado sin sesión`"| C5["`**AUD-05** · Funcional
Redirigido al login`"]:::err
  A3 -->|"`Contraseñas guardadas`"| C6["`**AUD-06** · Unitaria
Hash bcrypt, nunca texto plano`"]:::ok
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| AUD-01 | Observer de auditoría | Crear, editar o eliminar un modelo auditado | Registro con usuario, acción y fecha | Integración |
| AUD-02 | Observer de auditoría | Cambio de contraseña | La contraseña nunca se guarda en la auditoría | Unitaria |
| AUD-03 | Acceso a la auditoría | Administrador | Ve el listado | Integración |
| AUD-04 | Acceso a la auditoría | Cajero, Mesero o Cocinero | Respuesta 403 | Integración |
| AUD-05 | Rutas protegidas | Invitado sin sesión | Redirigido al login | Funcional |
| AUD-06 | Rutas protegidas | Contraseñas guardadas | Hash bcrypt, nunca texto plano | Unitaria |

## Reportes (pendiente) (RF028–RF032)

Clases: ReporteServiceTest y ReporteTest (por crear en el 3.er corte)

```mermaid
flowchart LR
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  R["`**Reportes (pendiente)**
RF028–RF032`"]:::raiz
  A1["`**Ventas por periodo**`"]:::accion
  R --> A1
  A2["`**Productos más vendidos**`"]:::accion
  R --> A2
  A3["`**Consumo de inventario**`"]:::accion
  R --> A3
  A4["`**Ocupación de mesas**`"]:::accion
  R --> A4
  A5["`**Indicadores financieros**`"]:::accion
  R --> A5
  A1 -->|"`Día, semana o mes con ventas`"| C1["`**REP-01** · Integración
Total y detalle del periodo`"]:::pend
  A1 -->|"`Periodo sin ventas`"| C2["`**REP-02** · Funcional
Mensaje informativo`"]:::pend
  A2 -->|"`Periodo con ventas`"| C3["`**REP-03** · Unitaria
Ordenados de mayor a menor`"]:::pend
  A3 -->|"`Periodo con pedidos`"| C4["`**REP-04** · Unitaria
Cantidad usada por producto`"]:::pend
  A4 -->|"`Periodo con pedidos`"| C5["`**REP-05** · Unitaria
Ocupación por mesa`"]:::pend
  A5 -->|"`Periodo con facturas`"| C6["`**REP-06** · Unitaria
Ventas, ingresos y recaudos`"]:::pend
  A5 -->|"`Usuario que no es Administrador`"| C7["`**REP-07** · Integración
Respuesta 403`"]:::pend
```

| ID | Acción | Condición | Resultado esperado | Tipo |
|---|---|---|---|---|
| REP-01 | Ventas por periodo | Día, semana o mes con ventas | Total y detalle del periodo | Integración |
| REP-02 | Ventas por periodo | Periodo sin ventas | Mensaje informativo | Funcional |
| REP-03 | Productos más vendidos | Periodo con ventas | Ordenados de mayor a menor | Unitaria |
| REP-04 | Consumo de inventario | Periodo con pedidos | Cantidad usada por producto | Unitaria |
| REP-05 | Ocupación de mesas | Periodo con pedidos | Ocupación por mesa | Unitaria |
| REP-06 | Indicadores financieros | Periodo con facturas | Ventas, ingresos y recaudos | Unitaria |
| REP-07 | Indicadores financieros | Usuario que no es Administrador | Respuesta 403 | Integración |

## Estados del pedido

```mermaid
flowchart TB
  classDef raiz fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  classDef accion fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef ok fill:#dcf1e4,stroke:#2f8f5b,color:#0f3320
  classDef err fill:#fbe3de,stroke:#c4472f,color:#3d120a
  classDef pend fill:#efefef,stroke:#888888,color:#333333,stroke-dasharray:4 3
  classDef estado fill:#e3eaf6,stroke:#3c5a99,color:#13213d
  classDef final fill:#1f3a68,stroke:#1f3a68,color:#ffffff
  P(["`**Pendiente**`"]):::estado
  EP(["`**En preparación**`"]):::estado
  L(["`**Listo**`"]):::estado
  E(["`**Entregado**`"]):::estado
  F(["`**Facturado**`"]):::final
  C(["`**Cancelado**`"]):::final
  P -->|"`EST-01 · Mesero envía a cocina`"| EP
  EP -->|"`EST-02 · Cocinero`"| L
  L -->|"`EST-03 · Mesero`"| E
  E -->|"`EST-04 · Cajero registra pago`"| F
  P -->|"`EST-05 · Mesero cancela`"| C
  P -.- X6["`**EST-06** · Unitaria
Pendiente → Listo
rechazado`"]:::err
  EP -.- X7["`**EST-07** · Unitaria
En preparación → Pendiente
rechazado`"]:::err
  L -.- X8["`**EST-08** · Unitaria
Listo → Facturado
rechazado`"]:::err
  E -.- X9["`**EST-09** · Unitaria
Entregado → Cancelado
rechazado`"]:::err
  F -.- X10["`**EST-10** · Unitaria
Facturado → cualquier estado
rechazado`"]:::err
  C -.- X11["`**EST-11** · Unitaria
Cancelado → cualquier estado
rechazado`"]:::err
  linkStyle 0 stroke:#2f8f5b,stroke-width:2px
  linkStyle 1 stroke:#2f8f5b,stroke-width:2px
  linkStyle 2 stroke:#2f8f5b,stroke-width:2px
  linkStyle 3 stroke:#2f8f5b,stroke-width:2px
  linkStyle 4 stroke:#2f8f5b,stroke-width:2px
  linkStyle 5 stroke:#c4472f,stroke-width:1.5px
  linkStyle 6 stroke:#c4472f,stroke-width:1.5px
  linkStyle 7 stroke:#c4472f,stroke-width:1.5px
  linkStyle 8 stroke:#c4472f,stroke-width:1.5px
  linkStyle 9 stroke:#c4472f,stroke-width:1.5px
  linkStyle 10 stroke:#c4472f,stroke-width:1.5px
```

| ID | Caso | Resultado esperado |
|---|---|---|
| EST-12 | Mesero intenta marcar Listo | Respuesta 403 |
| EST-13 | Cocinero intenta marcar Entregado | Respuesta 403 |
| EST-14 | Cocinero intenta registrar el pago | Respuesta 403 |
| EST-15 | Transición válida | Queda en el historial y en la auditoría |

## Matriz de permisos (RbacTest)

| Acción | Administrador | Cajero | Mesero | Cocinero |
|---|---|---|---|---|
| Gestionar usuarios y roles | 200 | 403 | 403 | 403 |
| Crear, editar y eliminar productos | 200 | 403 | 403 | 403 |
| Cambiar disponibilidad de productos | 200 | 403 | 403 | 200 |
| Crear y editar mesas | 200 | 403 | 403 | 403 |
| Reservar y liberar mesas | 200 | 403 | 200 | 403 |
| Crear, modificar y cancelar pedidos | 200 | 403 | 200 | 403 |
| Enviar pedido a cocina | 200 | 403 | 200 | 403 |
| Marcar pedido Listo | 200 | 403 | 403 | 200 |
| Marcar pedido Entregado | 200 | 403 | 200 | 403 |
| Generar factura y registrar pago | 200 | 200 | 403 | 403 |
| Aplicar descuento | 200 | 200 | 403 | 403 |
| Ver auditoría | 200 | 403 | 403 | 403 |
| Ver reportes | 200 | 403 | 403 | 403 |