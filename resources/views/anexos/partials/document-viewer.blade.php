<div class="container-fluid p-0">
    @if(in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @else
        <iframe src="{{ $fileUrl }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @endif
</div>