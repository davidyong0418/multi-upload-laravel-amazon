<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
      {{ Auth::user()->name }} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu" role="menu">
       @if(Auth::user()->can('customer') || Auth::user()->can('contributor'))
        <li>
        <a href="/customers/subscribe/multiple">
          <span class="fas fa-money-bill-alt"></span> @lang('common.credit'):
          @if(Auth::user()->credit)
          {{Auth::user()->credit->current_credit}}
          @else
          <small><i>No credit found</i></small>
          @endif
          | @lang('common.add')
        </a></li>

        @endif
        @if(Auth::user()->can('contributor'))
        <hr>
        <li><a href="/dashboard">
        <span class="fas fa-handshake"></span> @lang('common.balance'): {{Auth::user()->contributor()->balance}}  | @lang('common.manage')</a></li>
        <hr>
        @endif
        <li><a href="/change-password">
          <span class="fa fa-key"></span> @lang('common.change_password')</a></li>
        <li>
            <a href="{{ route('logout') }}"
                onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();">
                         <span class="fa fa-unlock"></span> @lang('common.logout')
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </li>
    </ul>
</li>
