<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}"
        ><i class="la la-home nav-icon"></i>
        {{ trans("backpack::base.dashboard") }}</a
    >
</li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"
        ><i class="nav-icon la la-group"></i> Crawler</a
    >
    <ul class="nav-dropdown-items">
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('/crawl/url') }}"
                ><i class="nav-icon la la-user"></i> <span>URL Manager</span></a
            >
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('/crawl/received') }}"
                ><i class="nav-icon la la-key"></i>
                <span>Data Received</span></a
            >
        </li>
    </ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user') }}'><i class='nav-icon la la-question'></i> Users</a></li>