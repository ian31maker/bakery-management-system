<?php
// Shared sidebar with breadcrumbs
$role = get_role();
$role_color = 'sidebar-' . $role;

$menu_items = [
    'admin' => [
        ['dashboard.php', '🏠 Dashboard'],
        ['manage_users.php', '👥 Manage Users'],
        ['all_orders.php', '📦 All Orders'],
        ['view_reports.php', '📊 Reports'],
        ['calendar.php', '📅 Calendar'],
        ['set_prices.php', '💰 Set Prices'],
    ],
    'cashier' => [
        ['dashboard.php', '🏠 Dashboard'],
        ['record_sale.php', '💰 Record Sale'],
        ['ticket_queue.php', '🎫 Ticket Queue'],
        ['sales_history.php', '📋 Sales History'],
    ],
    'baker' => [
        ['dashboard.php', '🏠 Dashboard'],
        ['view_inventory.php', '📦 Inventory'],
        ['update_stock.php', '➕ Update Stock'],
        ['pending_orders.php', '📋 Pending Orders'],
        ['production_log.php', '🏭 Production'],
        ['recipes.php', '📖 Recipes'],
        ['calendar.php', '📅 Calendar'],
    ],
    'driver' => [
        ['dashboard.php', '🏠 Dashboard'],
        ['assigned_deliveries.php', '📦 My Deliveries'],
        ['delivery_history.php', '📋 History'],
    ],
];

$current_page = basename($_SERVER['PHP_SELF']);
$current_name = str_replace('.php', '', $current_page);
$current_name = ucfirst(str_replace('_', ' ', $current_name));
?>

<div class="sidebar <?php echo $role_color; ?>">
    <h2>🥖 Bakery System</h2>
    
    <?php foreach ($menu_items[$role] as $item): ?>
        <a href="<?php echo $item[0]; ?>" class="<?php echo $current_page == $item[0] ? 'active' : ''; ?>">
            <?php echo $item[1]; ?>
        </a>
    <?php endforeach; ?>
    
    <a href="../logout.php" class="logout">🚪 Logout</a>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
        <small>Breadcrumb:</small><br>
        <small>Home > <?php echo $current_name; ?></small>
    </div>
</div>