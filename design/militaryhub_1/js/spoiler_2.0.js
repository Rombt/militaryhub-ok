/*
 *
 * 	Simple spoiler v 2.0
 *	The styles in the file spoiler.less
 *
 */

export default function Spoiler() {
  const spoilers = document.querySelectorAll('[data-rmbt-spoiler]');

  spoilers.forEach(spoiler => {
    const state = spoiler.dataset.rmbtSpoiler;
    const toggleSpoiler = spoiler.querySelector('[data-rmbt-spoiler-toggle]');
    const bodySpoiler = spoiler.querySelector('[data-rmbt-spoiler-body]');
    const titleSpoiler = spoiler.querySelector('[data-rmbt-spoiler-title]');
    const items = bodySpoiler.querySelectorAll(':scope > [data-rmbt-spoiler-item]');

    const heightSpoilerTitle = parseFloat(getComputedStyle(titleSpoiler).height);

    if (state == 'open') {
      spoilerOpen(spoiler, 'rmbt-spoiler-open');
    }

    toggleSpoiler.addEventListener('click', e => {
      if (spoiler.classList.contains('rmbt-spoiler-open')) {
        spoilerClose(spoiler, 'rmbt-spoiler-open', heightSpoilerTitle);
      } else {
        spoilerOpen(spoiler, 'rmbt-spoiler-open');
      }
    });

    items.forEach(item => {
      const itemToggle = item.querySelector(
        ':scope [data-rmbt-item-toggle]:not(:scope ul [data-rmbt-item-toggle])'
      );
      const itemBody = item.querySelector(
        ':scope [data-rmbt-item-body]:not(:scope ul [data-rmbt-item-body])'
      );

      if (!itemBody || !itemToggle) {
        return;
      }

      const state = item.dataset.rmbtSpoilerItem;
      const heightItem = getComputedStyle(item).height;

      const heightItemBody = itemBody.scrollHeight;

      // т.к. data-rmbt-item-toggle может быть обёрнут в какой либо блок, и не в один(!), для удобства вёрстки например,
      // то нужно учесть и его высоту
      let el = itemToggle;
      let prevEl = el;
      let heightWrapToggle = 0;
      let i = 0;
      while (el.parentElement) {
        i++;
        el = el.parentElement;
        if (el == item && i > 1) {
          const style = getComputedStyle(prevEl);
          const marginTop = parseFloat(style.marginTop) || 0;
          const marginBottom = parseFloat(style.marginBottom) || 0;
          heightWrapToggle = prevEl.offsetHeight + marginTop + marginBottom;

          break;
        }
        prevEl = el;
      }

      let currentHeightSpoiler;
      if (state == 'open') {
        currentHeightSpoiler = parseFloat(getComputedStyle(spoiler).height);
        spoiler.style.height = `${
          currentHeightSpoiler + heightWrapToggle + heightItemBody - 30 //todo что такое 30 разобраться!
        }px`;
        itemOpen(item, heightWrapToggle, itemBody, 'rmbt-item-open');
      }

      if (itemToggle && itemBody) {
        itemToggle.addEventListener('click', e => {
          currentHeightSpoiler = parseFloat(getComputedStyle(spoiler).height);

          if (item.classList.contains('rmbt-item-open')) {
            spoiler.style.height = `${
              currentHeightSpoiler -
              heightWrapToggle -
              heightItemBody +
              parseInt(heightItem)
            }px`;
            itemClose(item, heightItem, itemBody, 'rmbt-item-open');
          } else {
            spoiler.style.height = `${
              currentHeightSpoiler +
              heightWrapToggle +
              heightItemBody -
              parseInt(heightItem)
            }px`;
            itemOpen(item, heightWrapToggle, itemBody, 'rmbt-item-open');
          }
        });
      }
    });
  });
}

function itemOpen(item, heightWrapToggle, itemBody, classOpen) {
  const heightItemBody = itemBody.scrollHeight;

  item.classList.add(classOpen);
  itemBody.style.height = `${heightItemBody}px`;
  item.style.height = `${heightWrapToggle + heightItemBody}px`;
}

function itemClose(item, heightItem, itemBody, classOpen) {
  item.classList.remove(classOpen);
  itemBody.style.height = `${0}px`;
  item.style.height = heightItem;
}

function spoilerOpen(spoiler, classOpen) {
  spoiler.classList.add(classOpen);
  spoiler.style.height = `${spoiler.scrollHeight}px`;
}

function spoilerClose(spoiler, classOpen, heightSpoilerTitle) {
  spoiler.classList.remove(classOpen);
  spoiler.style.height = `${heightSpoilerTitle}px`;
}

//* ===================   Разметка вариант 1  =================================
/*
<div class="rmbt-mh-catalog-sidebar" data-rmbt-spoiler="open">
<div class="rmbt-mh-catalog-sidebar__main-title" data-rmbt-spoiler-title>

		<svg>
			<use href="./catalog/view/theme/militaryhub/image/sprite.svg#11_catalog"></use>
		</svg>
		<h4 class="rmbt-mh-catalog-sidebar__title-text">{{text_title}}</h4>
		<div class="rmbt-mh-catalog-sidebar__toggle" data-rmbt-spoiler-toggle>
			<svg>
				<use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use>
			</svg>
		</div>
	</div>
	<nav>
		<ul data-rmbt-spoiler-body>
			{% for category in categories %}
				<li class="rmbt-mh-catalog-sidebar__title" data-rmbt-spoiler-item>
					<a href="{{category.href}}">{{category.name}}</a>
					{% if category.children %}
						<div data-rmbt-item-toggle>
							<svg class="toggle-open">
								<use href="./catalog/view/theme/militaryhub/image/sprite.svg#31_minus"></use>
							</svg>
							<svg class="toggle-close">
								<use href="./catalog/view/theme/militaryhub/image/sprite.svg#32_plus"></use>
							</svg>
						</div>
						<ul data-rmbt-item-body>
							{% for child in category.children %}
								<li class="rmbt-mh-catalog-sidebar__item" data-rmbt-spoiler-item>
									<a href="{{child.href}}">{{child.name}}</a>
								</li>
							{% endfor %}
						</ul>
					{% endif %}
				</li>
			{% endfor %}
		</ul>
	</nav>
</div>
*/

//* ===================   Разметка вариант 2  =================================
/*
<div class="mh-filter-sidebar" data-rmbt-spoiler="open">
	<div class="mh-filter-sidebar__main-title" data-rmbt-spoiler-title>
		<svg>
			<use href="./catalog/view/theme/militaryhub/image/sprite.svg#33_filter"></use>
		</svg>
		<h4 class="mh-filter-sidebar__title-text">{{text_title}}</h4>
		<div class="mh-filter-sidebar__toggle" data-rmbt-spoiler-toggle>
			<svg>
				<use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use>
			</svg>
		</div>
	</div>
	<form id="mh-filter-form" class="mh-filter-sidebar__form" action="{{ action }}" method="get">
		<ul data-rmbt-spoiler-body>
			<li class="mh-filter-sidebar__price mh-filter-price" data-rmbt-spoiler-item="open">
				<div class="mh-filter-sidebar__title-wrap">
					<h5 class="mh-filter-sidebar__title">Цена</h5>
					<div class="mh-filter-sidebar__toggle-wrap" data-rmbt-item-toggle>
						<svg class="toggle-open">
							<use href="./catalog/view/theme/militaryhub/image/sprite.svg#31_minus"></use>
						</svg>
						<svg class="toggle-close">
							<use href="./catalog/view/theme/militaryhub/image/sprite.svg#32_plus"></use>
						</svg>
					</div>
				</div>
				<div class="mh-filter-price__body" data-rmbt-item-body>

					<div class="mh-filter-price__inputs">
						<input type="text" id="min-price" value="{{range_price.min}}">
						<span>
							&#8212;
						</span>
						<input type="text" id="max-price" value="{{range_price.max}}">
					</div>
					<div class="mh-filter-price__slider">
						<input type="range" name="min_price" id="range-min" min="{{range_price.min_prod}}" max="{{range_price.max_prod}}" value="{{range_price.min_filter}}">
						<input type="range" name="max_price" id="range-max" min="{{range_price.min_prod}}" max="{{range_price.max_prod}}" value="{{range_price.max_filter}}">
						<div id="progress"></div>

					</div>
					<button type="submit" class="mh-filter-price__btn-apply">
						Застосувати
						<svg>
							<use href="./catalog/view/theme/militaryhub/image/sprite.svg#36_check"></use>
						</svg>
					</button>
				</div>
			</li>

			{% if attributes.groups %}
				{% for group_attribute in attributes.groups %}
					{% if group_attribute.attributes|length > 0 %}
						{% for attribute in group_attribute.attributes %}
							{% if attribute.values|length > 0 %}
								<li class="mh-filter-attributes__group" data-rmbt-spoiler-item>
									<div class="mh-filter-sidebar__title-wrap">
										<h5 class="mh-filter-sidebar__title">{{ attribute.attribute_name }}</h5>
										<div class="mh-filter-sidebar__toggle-wrap" data-rmbt-item-toggle>
											<svg class="toggle-open">
												<use href="./catalog/view/theme/militaryhub/image/sprite.svg#31_minus"></use>
											</svg>
											<svg class="toggle-close">
												<use href="./catalog/view/theme/militaryhub/image/sprite.svg#32_plus"></use>
											</svg>
										</div>
									</div>

									<ul class="mh-filter-attributes__values-wrap" data-rmbt-item-body>
										{% for value in attribute.values %}
											<li class="mh-filter-attributes__values" data-rmbt-spoiler-item>
												<input type="checkbox" id="attr-{{ value.id }}" name="attr[]" value="{{ value.id }}" {% if value.id in attributes.checked %} checked {% endif %}>
												<label for="attr-{{ value.id }}">{{ value.name }}
													<span>({{ value.count }})</span>
												</label>
											</li>
										{% endfor %}
									</ul>
								</li>
							{% endif %}
						{% endfor %}
					{% endif %}
				{% endfor %}
			{% endif %}


		</ul>
	</form>
</div>
*/
