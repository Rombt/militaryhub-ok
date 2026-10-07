<div class="rmbt-mh-intro-section">
    <h1 class="rmbt-mh-intro-section__title">{$title|default:''}</h1>

    <div class="rmbt-mh-intro-section__slogan">{$slogan|default:''}</div>

    <div class="rmbt-mh-intro-section__description">{$description|default:''}</div>

    {include
        file="btn_link.tpl"
        text=$text_button|default:''
        link=$link|default:''
        icon_id='long_arrow'
    }
</div>
