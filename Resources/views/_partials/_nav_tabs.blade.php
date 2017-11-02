<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    @foreach(\Netcore\Translator\Helpers\TransHelper::getAllLanguages() as $language)
        <li
                role="presentation"
                class="{{ $loop->first ? 'active' : '' }}"
        >
            <a
                    href="#{{ $language->iso_code }}"
                    aria-controls="{{ $language->iso_code }}"
                    role="tab"
                    data-toggle="tab"
            >
                {{ $language->title_localized }}
            </a>
        </li>
    @endforeach
</ul>
