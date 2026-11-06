<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Patrón State: estados del pedido y sus transiciones válidas.
 */
enum EstadoPedido: string
{
    case Pendiente     = 'Pendiente';
    case EnPreparacion = 'En preparación';
    case Listo         = 'Listo';
    case Entregado     = 'Entregado';
    case Facturado     = 'Facturado';
    case Cancelado     = 'Cancelado';

    /** @return list<self> */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Pendiente     => [self::EnPreparacion, self::Cancelado],
            self::EnPreparacion => [self::Listo],
            self::Listo         => [self::Entregado],
            self::Entregado     => [self::Facturado],
            self::Facturado,
            self::Cancelado     => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }

    /**
     * RBAC: permiso necesario para LLEVAR un pedido a este estado desde la interfaz.
     * Facturado no se asigna a mano: lo hace el módulo de facturación al pagar.
     */
    public function permisoRequerido(): ?string
    {
        return match ($this) {
            self::EnPreparacion => 'enviar pedidos a cocina',
            self::Listo         => 'preparar pedidos',
            self::Entregado     => 'entregar pedidos',
            self::Cancelado     => 'cancelar pedidos',
            self::Pendiente,
            self::Facturado     => null,
        };
    }

    /** Texto del botón que lleva el pedido a este estado. */
    public function accion(): string
    {
        return match ($this) {
            self::EnPreparacion => 'Enviar a cocina',
            self::Listo         => 'Marcar como listo',
            self::Entregado     => 'Marcar como entregado',
            self::Cancelado     => 'Cancelar pedido',
            self::Facturado     => 'Facturar',
            self::Pendiente     => 'Reabrir',
        };
    }

    /** Clase de Bootstrap para la etiqueta del estado. */
    public function colorBadge(): string
    {
        return match ($this) {
            self::Pendiente     => 'bg-secondary',
            self::EnPreparacion => 'bg-warning text-dark',
            self::Listo         => 'bg-info text-dark',
            self::Entregado     => 'bg-primary',
            self::Facturado     => 'bg-success',
            self::Cancelado     => 'bg-danger',
        };
    }

    /** HU018: se pueden agregar productos mientras no esté Entregado, Facturado ni Cancelado. */
    public function admiteNuevosProductos(): bool
    {
        return in_array($this, [self::Pendiente, self::EnPreparacion, self::Listo], true);
    }

    /** HU019 / HU020: solo se modifica o cancela en Pendiente. */
    public function esEditable(): bool
    {
        return $this === self::Pendiente;
    }

    /** Pedido que todavía ocupa la mesa. */
    public function estaActivo(): bool
    {
        return ! in_array($this, [self::Facturado, self::Cancelado], true);
    }

    public function esFinal(): bool
    {
        return $this->transicionesPermitidas() === [];
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return list<string> */
    public static function valoresActivos(): array
    {
        return array_values(array_map(
            fn (self $estado) => $estado->value,
            array_filter(self::cases(), fn (self $estado) => $estado->estaActivo())
        ));
    }
}
