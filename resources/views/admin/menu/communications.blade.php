{{-- COMMUNICATIONS sidebar group (auto-included by admin/layouts/app.blade.php) --}}
<li class="nav-item has-treeview {{ request()->is('admin/emails*') || request()->is('admin/email-logs*') || request()->is('admin/queue-monitor*') || request()->is('admin/emails*') || request()->is('admin/email-logs*') || request()->is('admin/queue-monitor*') || request()->is('admin/contacts*') || request()->is('admin/contact-templates*') || request()->is('admin/notifications*') || request()->is('freequote*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->is('admin/emails*') || request()->is('admin/email-logs*') || request()->is('admin/queue-monitor*') || request()->is('admin/contacts*') || request()->is('admin/contact-templates*') || request()->is('admin/notifications*') || request()->is('freequote*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bullhorn"></i>
        <p>Communications <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.emails.index') }}" class="nav-link {{ request()->is('admin/emails*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-envelope-open-text"></i><p>Email Templates</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.email-logs.index') }}" class="nav-link {{ request()->is('admin/email-logs*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-alt"></i><p>Email Logs</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.queue.monitor') }}" class="nav-link {{ request()->is('admin/queue-monitor*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tasks"></i><p>Queue Monitor</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-bell"></i><p>Notifications</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('freequote.index') }}" class="nav-link {{ request()->is('freequote*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-signature"></i><p>Quote Requests</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->is('admin/contacts*') || request()->is('admin/contact-templates*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-inbox"></i><p>Contact Messages</p>
            </a>
        </li>
    </ul>
</li>
