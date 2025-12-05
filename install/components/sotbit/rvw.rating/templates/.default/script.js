function sotbitReviewsInitializeRatingEvents(instanceId) {
  let offset = -80;

  function scrollToReviewsElement(element) {
    if (element) {
      const y = element.getBoundingClientRect().top + window.scrollY + offset;
      window.scrollTo({ top: y, behavior: 'smooth' });
    }
  }

  function findReviewsContainer() {
    return document.getElementById('sotbit-rvw-reviews');
  }

  document.getElementById(instanceId)?.addEventListener('click', () => {
    const element = document.getElementById('sotbit-rvw-reviews');

    // reviews tab might exist but be inactive, so we try to switch to reviews tab
    if (!element) {
      const reviewsTabButton = document.querySelector('.btn-reviews[data-type="reviews"]');

      if (reviewsTabButton) {
        reviewsTabButton.click();

        BX.addCustomEvent('sotbit:reviews.tabChange', (type) => {
          if (type === 'reviews') {
            setTimeout(() => {
              scrollToReviewsElement(findReviewsContainer());
            });
          }
        });
      }
    } else {
      scrollToReviewsElement(element);
    }
  });
}
