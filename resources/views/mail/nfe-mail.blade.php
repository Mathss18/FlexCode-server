@component('mail::message')
<h1>{{$titulo}}</h1>

<p>{{$conteudo}}<p>

<!-- @component('mail::button', ['url' => ''])
Baixar
@endcomponent -->

Obrigado,<br>
{{ session('tenant')->nome }}
@endcomponent
