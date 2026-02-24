<div>
    <span class="d-block">
        {{ isDemoMode() ? '+***********' :  @$client->primaryUser->phone }}
    </span>
    @if ( @$client->primaryUser->country)
    <span class="d-block">
        {{ @$client->primaryUser->country->name }}
    </span>  
    @endif
</div>
