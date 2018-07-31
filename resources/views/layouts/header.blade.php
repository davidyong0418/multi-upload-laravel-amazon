<nav class="topbar">
<span class="topbar-message">{{ session('topbar_message') }}</span>
</nav>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                <!--{{ config('app.name', 'Laravel') }}-->
                <img src="/img/logo.png" alt="Yohka" >
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                &nbsp;
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ route('login') }}">@lang('common.login')</a></li>
                    <li><a href="{{ route('register') }}">@lang('common.register')</a></li>
                @else
                    <!-- Authenticated Links (Common to all users) -->
                    @if (Auth::user()->can('admin'))
                        <li><a href="/contributors">@lang('common.contributors')</a></li>
                        <li><a href="/users">@lang('common.users')</a></li>
                        <li><a href="/artworks">@lang('common.artworks')</a></li>
                        <li><a href="/subscriptions">@lang('common.subscriptions')</a></li>
                    @endif
                    @if (Auth::user()->can('contributor') || Auth::user()->can('customer'))
                        <li><a href="{{ route('dashboard') }}">@lang('dashboard.dashboard')</a></li>
                    @endif
                    @if ( Auth::user()->can('customer'))
                        <li><a href="/contribute">@lang('common.contribute')</a></li>
                        <li><a href="/customers/subscribe/multiple">@lang('common.subscribe')</a></li>
                    @endif
                    @include('layouts.header-account-menu')
                @endif
            </ul>
        </div>
    </div>
</nav>
