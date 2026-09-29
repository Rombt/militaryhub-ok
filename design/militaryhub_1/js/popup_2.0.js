const mainContainerClass = "rmbt-page-wrap"; // class your main container
//todo получать продолжительность анимации автоматичиски прочитав значени свайства transition
const timeout = 800; // the quantity  of milliseconds must be equal to the animation time in the 'transition' property in the file popup.less

const nl_popupToggles = document.querySelectorAll("[data-rmbt-popup-target]");
const body = document.querySelector("body");
const lockPadding = document.querySelectorAll(".lock-padding"); // для элементов с position:fixed;
const bodyLockClass = "rmbt-lock";

let unLock = true;

console.log("00 **********************************");

function popupOpen(currentPopup) {
  if (currentPopup && unLock) {
    const popupActive = document.querySelector(".rmbt-popup.rmbt-popup-open");
    if (popupActive) {
      closeAllPopupsOpen(false);
    } else {
      bodyLock();
    }

    document
      .querySelector(`[data-rmbt-popup-target="${currentPopup.id}"]`)
      .toggleAttribute("rmbt-popup-open");

    currentPopup.classList.add("rmbt-popup-open");
    currentPopup.addEventListener("click", function (e) {
      if (!e.target.closest(".rmbt-popup__container")) {
        popupClose(e.target.closest(".rmbt-popup"));
      }
    });
  }
}

export function popupClose(popupActive, downLock = true) {
  if (unLock) {
    popupActive.classList.remove("rmbt-popup-open");
    document
      .querySelector(`[data-rmbt-popup-target="${popupActive.id}"]`)
      .toggleAttribute("rmbt-popup-open");
    if (downLock) {
      bodyunLock();
    }
  }
}

function bodyLock() {
  const lockPaddingValue =
    window.innerWidth - document.documentElement.clientWidth + "px";

  if (lockPadding.length > 0) {
    for (let i = 0; i < lockPadding.length; i++) {
      const el = lockPadding[i];
      el.style.paddingRight = lockPaddingValue;
    }
  }
  body.style.paddingRight = lockPaddingValue;
  body.classList.add(bodyLockClass);
  unLock = false;
  setTimeout(function () {
    unLock = true;
  }, timeout);
}

function bodyunLock() {
  setTimeout(function () {
    if (lockPadding.length > 0) {
      for (let i = 0; i < lockPadding.length; i++) {
        const el = lockPadding[i];
        el.style.paddingRight = "0px";
      }
    }
    body.style.paddingRight = "0px";
    body.classList.remove(bodyLockClass);
  }, timeout);
  unLock = false;
  setTimeout(function () {
    unLock = true;
  }, timeout);
}

function closeAllPopupsOpen(downLock = true) {
  const nl_PopupsOpen = document.querySelectorAll(".rmbt-popup-open");
  if (nl_PopupsOpen.length > 0) {
    nl_PopupsOpen.forEach((PopupOpen) => {
      popupClose(PopupOpen, downLock);
    });
  }
}

export function Popups() {
  console.log("11 **********************************");
  if (nl_popupToggles.length > 0) {
    nl_popupToggles.forEach((popupToggle) => {
      popupToggle.addEventListener("click", function (e) {
        // e.preventDefault(); //?? a оно здесь нужно

        const popupId = popupToggle.dataset.rmbtPopupTarget;
        const currentPopup = document.getElementById(popupId);

        const idBtnPopupClose = popupToggle.dataset.rmbtPopupClose;

        if (idBtnPopupClose) {
          const btnPopupClose = document.getElementById(idBtnPopupClose);
          btnPopupClose.addEventListener("click", (e) => {
            popupClose(currentPopup);
          });
        }

        if (popupToggle.hasAttribute("rmbt-popup-open")) {
          popupClose(currentPopup);
        } else {
          popupOpen(currentPopup);
        }
      });
    });
  }

  // if (nl_popupClose.length > 0) {
  //   nl_popupClose.forEach(popupClose => {
  //     popupClose.addEventListener('click', function (e) {
  //       // e.preventDefault(); //?? a оно здесь нужно

  //       const popupId = popupClose.dataset.rmbtPopupTarget;
  //       const currentPopup = document.getElementById(popupId);
  //       popupClose(currentPopup);
  //     });
  //   });
  // }

  document.addEventListener("keydown", function (e) {
    if (e.which === 27) {
      closeAllPopupsOpen();
    }
  });
}

/*

  //* класу rmbt-popup намеренно не задано position:relative для того что бы popup был зависим от родителя в который он вставлен
  
  <button type="button" class="rmbt-mh-catalog-button" data-rmbt-popup-target="catalog-popup-header" data-rmbt-popup-close="catalog-popup-header">

    <div class="rmbt-mh-catalog-button__icons-wrap">
    <svg id='rmbt-mh-catalog-button-close'>

      <use href="./catalog/view/theme/militaryhub/image/sprite.svg#64_plus"></use>
    </svg>
    <svg id='rmbt-mh-catalog-button-open'>
      <use href="./catalog/view/theme/militaryhub/image/sprite.svg#11_catalog"></use>
    </svg>
    
    </div>

    Каталог товарів
  </button>



<div class="rmbt-popup" id="catalog-popup-header">
	<div class="rmbt-popup__overlay"></div>
	<div class="rmbt-popup__container">
		<main class="rmbt-popup__content">
			Контент модального окна
		</main>
		<div class="rmbt-popup__right-column"></div>
	</div>
</div>

*/
