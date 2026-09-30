<li class="nav-item has-treeview {{ request()->is('admin/cms*') ? 'menu-open' : '' }}">
  <a href="#" class="nav-link {{ request()->is('admin/cms*') ? 'active' : '' }}">
    <i class="nav-icon fa fa-layer-group"></i>
    <p>CMS <i class="right fa fa-angle-left"></i></p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('admin.cms.posts.index') }}?type=page"
         class="nav-link {{ request()->is('admin/cms/posts*') && request('type', 'page') === 'page' ? 'active' : '' }}">
        <i class="nav-icon fa fa-file-alt"></i><p>Pages</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.posts.index') }}?type=blog_post"
         class="nav-link {{ request()->is('admin/cms/posts*') && request('type') === 'blog_post' ? 'active' : '' }}">
        <i class="nav-icon fa fa-blog"></i><p>Blog Posts</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.posts.index') }}?type=product"
         class="nav-link {{ request()->is('admin/cms/posts*') && request('type') === 'product' ? 'active' : '' }}">
        <i class="nav-icon fa fa-shopping-bag"></i><p>Products</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.posts.index') }}?type=service"
         class="nav-link {{ request()->is('admin/cms/posts*') && request('type') === 'service' ? 'active' : '' }}">
        <i class="nav-icon fa fa-cogs"></i><p>Services</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.taxonomies.index') }}"
         class="nav-link {{ request()->is('admin/cms/taxonomies*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-tags"></i><p>Categories &amp; Tags</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.menus.index') }}"
         class="nav-link {{ request()->is('admin/cms/menus*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-bars"></i><p>Menus</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.cms.media.index') }}"
         class="nav-link {{ request()->is('admin/cms/media*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-images"></i><p>Media Library</p>
      </a>
    </li>
      <li class="nav-item">
      <a href="{{ route('admin.blog-import.index') }}"
         class="nav-link {{ request()->is('admin/blog-import*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-file-import"></i><p>Blog Import</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('admin.site-sections.index') }}"
         class="nav-link {{ request()->is('admin/site-sections*') ? 'active' : '' }}">
        <i class="nav-icon fa fa-layer-group"></i><p>Site Sections</p>
      </a>
    </li>
</ul>
</li>
