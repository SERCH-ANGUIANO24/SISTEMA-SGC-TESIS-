{{-- ESTE CODIGO MUESTRA "UNA MIGA DE PAN" ES DECIR INDICA EN QUE CARPETA ACTUAL TE ENCUENTRAS EN LA RUTA DE NAVEGACION DE LAS CARPETAS DENTRO DEL MODULO DE ANEXOS, PERMITE AL USUARIOS SABER 
EN QUE CARPETA SE ENCUENTRA Y ASI MISMO PODER RETROCEDER A CUALQUIER CARPETA PRINCIPAL SI ES QUE ESTA DENTRO DE UNA SUBCARPETA*/
MUESTRA LA RUTAS DE NAVEGACION SI HAY CARPETAS PARA MOSTRAR DENTRO DEL MODULO EN ESE CASO CUANDO SE INGRESA A UNA CARPETA MUESTRA LA 
RUTA DE NAVEGACION DE CARPETAS--}}
@if($breadcrumbs->count() > 0 || $currentFolder)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-3 rounded-3">
    {{--CUANDO SE ESTA DENTRO DE UNA CARPETA SE MUESTRA LAS RUTAS DEBAJO DEL NOMBRE DEL MODULO ("ANEXOS") Y EL ICONO DE CASA CON EL NOMBRE DE  "RAIZ" INDICA QUE ES EL LUGAR PRINCIPAL 
    DONDE APARECEN LAS CARPETAS--}}
        <li class="breadcrumb-item">
            <a href="{{ route('anexos.index') }}" class="text-decoration-none">
                <i class="bi bi-house-door"></i> Raíz
            </a>
        </li>
    {{--AQUI ES DONDE RECORREN LA JERARQUIA DE CAREPTAS, ES DECIR QUE CUANDO SE ESTA DENTRO DE UNA CARPETA SE MUESTRAN LOS NOMBRES DE LAS CAREPTAS
    EJEM: REGLAMENTOS/REGLAMENTOS EXTERNOS/REGLAS ESCOLARES ES DECIR EL BREADCRUMB TOMA EN CUENTA COMO SE GUARDA ESAS JERARQUIAS COMO  --}}
        @foreach($breadcrumbs as $folder)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->name }}
                </li>
            {{-- ESTO INDICA QUE CUANDO ESTAMOS DENTRO DE UNA CARPETAS UY HAY MAS CARPETAS EN LA RUTA DE NAVEGACION 
            SE PUEDE RETROCEDER DANDOLE CLICK PARA REGRESAR POR MEDIO DE UN ENLACE--}}
            @else
                <li class="breadcrumb-item">
                    <a href="{{ route('anexos.index', ['folder' => $folder->id]) }}" class="text-decoration-none">
                        <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->name }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif