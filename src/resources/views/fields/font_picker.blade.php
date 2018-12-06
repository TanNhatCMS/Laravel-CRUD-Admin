<!-- select2 google fonts picker -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <select
        name="{{ $field['name'] }}@if (isset($field['allows_multiple']) && $field['allows_multiple']==true)[]@endif"
        style="width: 100%"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2_google_fonts_picker'])
        @if (isset($field['allows_multiple']) && $field['allows_multiple']==true)multiple @endif
        >

        @if (isset($field['allows_null']) && $field['allows_null']==true)
            <option value="">-</option>
        @endif

        <?php 
            $default_options = ["Aclonica", "Allan", "Annie Use Your Telescope", "Anonymous Pro", "Allerta Stencil", "Allerta", "Amaranth", "Anton", "Architects Daughter", "Arimo", "Artifika", "Arvo", "Asset", "Astloch", "Bangers", "Bentham", "Bevan", "Bigshot One", "Bowlby One", "Bowlby One SC", "Brawler", "Buda:300", "Cabin", "Calligraffitti", "Candal", "Cantarell", "Cardo", "Carter One", "Caudex", "Cedarville Cursive", "Cherry Cream Soda", "Chewy", "Coda", "Coming Soon", "Copse", "Corben:700", "Cousine", "Covered By Your Grace", "Crafty Girls", "Crimson Text", "Crushed", "Cuprum", "Damion", "Dancing Script", "Dawning of a New Day", "Didact Gothic", "Droid Sans", "Droid Sans Mono", "Droid Serif", "EB Garamond", "Expletus Sans", "Fontdiner Swanky", "Forum", "Francois One", "Geo", "Give You Glory", "Goblin One", "Goudy Bookletter 1911", "Gravitas One", "Gruppo", "Hammersmith One", "Holtwood One SC", "Homemade Apple", "Inconsolata", "Indie Flower", "IM Fell DW Pica", "IM Fell DW Pica SC", "IM Fell Double Pica", "IM Fell Double Pica SC", "IM Fell English", "IM Fell English SC", "IM Fell French Canon", "IM Fell French Canon SC", "IM Fell Great Primer", "IM Fell Great Primer SC", "Irish Grover", "Irish Growler", "Istok Web", "Josefin Sans", "Josefin Slab", "Judson", "Jura", "Jura:500", "Jura:600", "Just Another Hand", "Just Me Again Down Here", "Kameron", "Kenia", "Kranky", "Kreon", "Kristi", "La Belle Aurore", "Lato:100", "Lato:100italic", "Lato:300",  "Lato", "Lato:bold",   "Lato:900", "League Script", "Lekton",   "Limelight",   "Lobster", "Lobster Two", "Lora", "Love Ya Like A Sister", "Loved by the King", "Luckiest Guy", "Maiden Orange", "Mako", "Maven Pro", "Maven Pro:500", "Maven Pro:700", "Maven Pro:900", "Meddon", "MedievalSharp", "Megrim", "Merriweather", "Metrophobic", "Michroma", "Miltonian Tattoo", "Miltonian", "Modern Antiqua", "Monofett", "Molengo", "Montserrat","Mountains of Christmas", "Muli:300",  "Muli",  "Neucha", "Neuton", "News Cycle", "Nixie One", "Nobile", "Nova Cut", "Nova Flat", "Nova Mono", "Nova Oval", "Nova Round", "Nova Script", "Nova Slim", "Nova Square", "Nunito:light", "Nunito", "OFL Sorts Mill Goudy TT", "Old Standard TT", "Open Sans:300", "Open Sans", "Open Sans:600", "Open Sans:800", "Open Sans Condensed:300", "Orbitron", "Orbitron:500", "Orbitron:700", "Orbitron:900", "Oswald", "Over the Rainbow", "Reenie Beanie", "Pacifico", "Patrick Hand", "Paytone One",  "Permanent Marker", "Philosopher", "Play", "Playfair Display", "Podkova", "PT Sans", "PT Sans Narrow", "PT Sans Narrow:regular,bold", "PT Serif", "PT Serif Caption", "Puritan", "Quattrocento", "Quattrocento Sans", "Radley", "Raleway", "Raleway:100", "Redressed", "Rock Salt", "Rokkitt", "Roboto", "Ruslan Display", "Schoolbell", "Shadows Into Light", "Shanti", "Sigmar One", "Six Caps", "Slackey", "Smythe", "Sniglet:800", "Special Elite", "Stardos Stencil", "Sue Ellen Francisco", "Sunshiney", "Swanky and Moo Moo", "Syncopate", "Tangerine", "Tenor Sans", "Terminal Dosis Light", "The Girl Next Door", "Tinos", "Ubuntu", "Ultra", "Unkempt", "UnifrakturCook:bold", "UnifrakturMaguntia", "Varela", "Varela Round", "Vibur", "Vollkorn", "VT323", "Waiting for the Sunrise", "Wallpoet", "Walter Turncoat", "Wire One", "Yanone Kaffeesatz", "Yanone Kaffeesatz:300", "Yanone Kaffeesatz:400", "Yanone Kaffeesatz:700", "Yeseva One", "Zeyada"];
            if(!isset($field['options'])){
                $field['options'] = array_combine($default_options, $default_options);
            }
        ?>
        @foreach ($field['options'] as $key => $value)
            @if((old($field['name']) && ($key == old($field['name']) || is_array(old($field['name'])) && in_array($key, old($field['name'])))) || (is_null(old($field['name'])) && isset($field['value']) && ($key == $field['value'] || (is_array($field['value']) && in_array($key, $field['value'])))) || (isset($field['default']) && (is_string($field['default']) && $field['default'] === $key || is_array($field['default']) && in_array($key, $field['default']) ) ) )
                <option value="{{ $key }}" selected>{{ $value }}</option>
            @else
                <option value="{{ $key }}">{{ $value }}</option>
            @endif
        @endforeach
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <!-- include select2 css-->
    <link href="{{ asset('vendor/adminlte/plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=<?= implode('|', array_map('urlencode', array_keys($field['options']))) ?>" rel="stylesheet" type="text/css">
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('vendor/adminlte/plugins/select2/select2.min.js') }}"></script>
    <script>
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            $('.select2_google_fonts_picker').each(function (i, obj) {
                if (!$(obj).hasClass("select2-hidden-accessible")){
                    $(obj)
                        .select2({
                            theme: "bootstrap",
                            templateResult: function (opt) {
                                if (!opt.id) {
                                    return opt.text;
                                }
                                var $state = $('<span style="font-family:\'' + opt.element.value + '\';"> ' + opt.text + '</span>');
                                return $state;
                            },
                        })
                        .on('change', function (e) {
                            var $el = $(this);
                            var font = $el.val();
                            var id = '#select2-' + $el.attr("id") + '-container';
                            $(id).css("font-family", "'" + font + "'");
                        })
                        .trigger('change')
                    ;
                }
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
