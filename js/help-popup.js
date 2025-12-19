(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    const helpButton = document.querySelector('.multi-login-help-icon');
    const helpDialog = document.getElementById('multi-login-help-dialog');
    const helpContent = document.getElementById('multi-login-help-content');
    const closeButton = document.querySelector('.multi-login-help-close');

    if (!helpButton || !helpDialog) {
      return;
    }

    const helpUrl = helpButton.dataset.helpUrl;

    // Open dialog when help button is clicked
    helpButton.addEventListener('click', function() {
      if (!helpDialog.open) {
        loadHelpContent(helpUrl);
        helpDialog.showModal();
      }
    });

    // Close dialog when close button is clicked
    if (closeButton) {
      closeButton.addEventListener('click', function() {
        helpDialog.close();
      });
    }

    // Close dialog when clicking outside (on backdrop)
    helpDialog.addEventListener('click', function(event) {
      if (event.target === helpDialog) {
        helpDialog.close();
      }
    });

    // Close dialog on Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && helpDialog.open) {
        helpDialog.close();
      }
    });

    /**
     * Load help content from the specified URL.
     */
    function loadHelpContent(url) {
      // Use Drupal.url to properly resolve the path
      if (typeof Drupal !== 'undefined' && typeof Drupal.url === 'function') {
        url = Drupal.url(url);
      } else if (!url.startsWith('/') && !url.startsWith('http')) {
        // Fallback: ensure the URL starts with /
        url = '/' + url;
      }

      // Show loading message
      helpContent.innerHTML = '<p><em>Loading...</em></p>';

      fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to load help page');
          }
          return response.text();
        })
        .then(html => {
          // Parse the HTML and extract main content
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Try to find main content area
          let content = doc.querySelector('main') ||
                        doc.querySelector('[role="main"]') ||
                        doc.querySelector('.content') ||
                        doc.querySelector('article') ||
                        doc.body;

          if (content) {
            helpContent.innerHTML = content.innerHTML;
          } else {
            helpContent.innerHTML = html;
          }
        })
        .catch(error => {
          console.error('Error loading help content:', error);
          helpContent.innerHTML = '<p style="color: red;"><strong>Error loading help page.</strong></p>';
        });
    }
  });
})();
