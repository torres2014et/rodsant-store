<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Catálogo central de permisos del sistema.
 *
 * Tener un único origen de verdad evita "strings mágicos" dispersos y mantiene
 * sincronizados el seeder, las policies y los gates.
 */
enum Permission: string
{
    // Productos
    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsEdit = 'products.edit';
    case ProductsDelete = 'products.delete';

    // Categorías
    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesEdit = 'categories.edit';
    case CategoriesDelete = 'categories.delete';

    // Inventario
    case InventoryView = 'inventory.view';
    case InventoryEdit = 'inventory.edit';

    // Pedidos
    case OrdersView = 'orders.view';
    case OrdersEdit = 'orders.edit';
    case OrdersExport = 'orders.export';

    // Clientes
    case CustomersView = 'customers.view';

    // Banners
    case BannersView = 'banners.view';
    case BannersCreate = 'banners.create';
    case BannersEdit = 'banners.edit';
    case BannersDelete = 'banners.delete';

    // Configuración (sólo Super Admin)
    case SettingsManage = 'settings.manage';

    // Usuarios y roles (sólo Super Admin)
    case UsersManage = 'users.manage';

    /**
     * Todos los permisos del sistema.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_map(static fn (self $p): string => $p->value, self::cases());
    }

    /**
     * Permisos que NO debe tener el rol Administrador (reservados a Super Admin).
     *
     * @return list<string>
     */
    public static function superAdminOnly(): array
    {
        return [
            self::SettingsManage->value,
            self::UsersManage->value,
        ];
    }

    /**
     * Permisos asignados al rol Administrador (todos menos los reservados).
     *
     * @return list<string>
     */
    public static function forAdministrator(): array
    {
        return array_values(array_diff(self::all(), self::superAdminOnly()));
    }
}
