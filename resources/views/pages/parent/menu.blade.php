{{--My Children--}}
<li class="nav-item">
    <a href="{{ route('my_children') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_children']) ? 'active' : '' }}"><i class="icon-users4"></i> My Children</a>
    <a href="{{ route('select_class') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['select_class']) ? 'active' : '' }}"><i class="icon-users4"></i> Application</a>

</li>