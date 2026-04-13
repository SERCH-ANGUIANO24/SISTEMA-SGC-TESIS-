{{--VISUALIZADOR DE ARCHIVOS DEL MODULO DE ANEXOS--}}
{{--ESTE CODIGO HACE FUNCIONAR A UN VISUALIZADOR DE ARCHIVOS QUE AL DAR CLICK EN EL BOTON DE VER DOCUMENTO
SE ABRE UN MODAL QUE VISUALIZA EL DOCUMENTO O ARCHIVO SIN EMBARGO SOLO FUNCIONA CON ARCHIVOS TXT, IMAGENES Y PDFS--}}

<div class="container-fluid p-0">
    @if(in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @else
    {{--SE ABRE EL VISUALIZADOR DE ARCHIVOS DEL NAVEGADOR--}}
        <iframe src="{{ $fileUrl }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @endif
</div>