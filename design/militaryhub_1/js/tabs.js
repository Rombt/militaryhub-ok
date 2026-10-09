export default function Tabs(blockName) {
  const tabsContainers = document.querySelectorAll(`.rmbt-${blockName}-tabs`);

  if (tabsContainers) {
    tabsContainers.forEach(tabsContainer => {
      tabsContainer.addEventListener('click', e => {
        const tab = e.target.closest(`.rmbt-${blockName}-tabs__title`);
        if (!tab) return;

        const tabName = tab.dataset.tab;
        const tabsTitles = tabsContainer.querySelectorAll(
          `.rmbt-${blockName}-tabs__title`
        );
        tabsTitles.forEach(tabTitle => {
          if (tabTitle === tab) {
            tabTitle.classList.add(`rmbt-${blockName}-tabs__title-active`);
          } else {
            tabTitle.classList.remove(`rmbt-${blockName}-tabs__title-active`);
          }
        });

        const tabsItems = tabsContainer.querySelectorAll(`.rmbt-${blockName}-tabs__body`);
        tabsItems.forEach(tabItem => {
          if (tabItem.getAttribute('data-tab-name') === tabName) {
            tabItem.classList.add(`rmbt-${blockName}-tabs__body-active`);
          } else {
            tabItem.classList.remove(`rmbt-${blockName}-tabs__body-active`);
          }
        });
      });
    });
  }
}

/*

<div class="rmbt-${blockName}-tabs">
  <nav>
    <div class="rmbt-${blockName}-tabs__title rmbt-${blockName}-tabs__title-active" data-tab="Військовий одяг">
      Військовий одяг
      <svg id="rmbt-mh-catalog-button-open">
        <use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use>
      </svg>
    </div>
    <div class="rmbt-${blockName}-tabs__title" data-tab="Комплекти для полювання та риболовлі">
      Комплекти для полювання та риболовлі
      <svg id="rmbt-mh-catalog-button-open">
        <use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use>
      </svg>
    </div>
  </nav>
  <div class="rmbt-${blockName}-tabs__content">
    <div class="rmbt-${blockName}-tabs__body rmbt-${blockName}-tabs__body-active" data-tab-name="Військовий одяг">
      
    </div>
    <div
      class="rmbt-${blockName}-tabs__body "
      data-tab-name="Комплекти для полювання та риболовлі"></div>
  </div>
</div>;

*/
