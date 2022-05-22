<tr>
<td class="header">
<a style="display: inline-block;">
@if (true)
<img src="{{session('config')->logo}}" class="logo" alt="{{session('tenant')->nome}}">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
