/**
 * pp-admin.js
 */
(function ($) {
  'use strict';
  $(window).on('load', function () {
    // console.log('Power Plugins Admin : load');

    if (typeof pwplData !== 'undefined') {
      // console.log('Power Plugins Admin : init');

      pwplData.posts = {};
      pwplData.taxonomies = {};

      const getPostMeta = (postId) => {
        let meta = null;

        if (typeof pwplData.posts[postId] !== 'undefined') {
          meta = pwplData.posts[postId];
        }

        return meta;
      };

      const getTermMeta = (taxonomySlug, termId) => {
        let meta = null;
        const index = termId.toString();

        if (typeof pwplData.taxonomies[taxonomySlug] === 'undefined') {
          // ...
        } else if (typeof pwplData.taxonomies[taxonomySlug][index] === 'undefined') {
          // ...
        } else {
          meta = pwplData.taxonomies[taxonomySlug][index];
        }

        return meta;
      };

      const isPostLoaded = (postId) => {
        return getPostMeta(postId) !== null;
      };

      const isTermLoaded = (taxonomySlug, termId) => {
        return getTermMeta(taxonomySlug, termId) !== null;
      };

      const getUniquePostOrTermTitle = (postOrTermMeta) => {
        return `${postOrTermMeta.title} (${postOrTermMeta.id})`;
      };

      const findPostMeta = (uniqueTitle) => {
        let foundPost = null;

        for (const postId in pwplData.posts) {
          const testPost = pwplData.posts[postId];
          const testTitle = getUniquePostOrTermTitle(testPost);

          // console.log( `Test: ${uniqueTitle} => ${testTitle}`);

          if (uniqueTitle === testTitle) {
            // console.log( 'break' );
            foundPost = testPost;
            break;
          }
        }

        return foundPost;
      };

      const findTermMeta = (taxonomySlug, uniqueTitle) => {
        let foundTerm = null;

        if (typeof pwplData.taxonomies[taxonomySlug] !== 'undefined') {
          // ...
          for (const termId in pwplData.taxonomies[taxonomySlug]) {
            const testTerm = pwplData.taxonomies[taxonomySlug][termId];
            const testTitle = getUniquePostOrTermTitle(testTerm);

            // console.log( `Test: ${uniqueTitle} => ${testTitle}`);

            if (uniqueTitle === testTitle) {
              // console.log( 'break' );
              foundTerm = testTerm;
              break;
            }
          }
        }

        return foundTerm;
      };

      const storePostsAndTermsFromResponse = (responseData) => {
        if (responseData.posts) {
          for (const itemId in responseData.posts) {
            const postMeta = responseData.posts[itemId];
            if (postMeta.id && postMeta.title && postMeta.slug) {
              console.log(`Store Post: ${postMeta.title}`);
              pwplData.posts[postMeta.id.toString()] = postMeta;
            }
          }
        }

        if (responseData.taxonomies) {
          for (const taxonomySlug in responseData.taxonomies) {
            let taxonomyTerms = responseData.taxonomies[taxonomySlug];
            for (const itemId in taxonomyTerms) {
              let termMeta = taxonomyTerms[itemId];
              // console.log( 'Term' );
              // console.log( taxonomyTerms[itemId] );
              if (termMeta.id && termMeta.title && termMeta.slug) {
                if (typeof pwplData.taxonomies[taxonomySlug] === 'undefined') {
                  pwplData.taxonomies[taxonomySlug] = {};
                }

                pwplData.taxonomies[taxonomySlug][itemId.toString()] = termMeta;
              }
            }
          }
        }
      };

      const fetchMissingPostsAndTerms = () => {
        const ajaxRequest = {
          action: pwplData.getPostAndTermMeta.action,
          nonce: pwplData.getPostAndTermMeta.nonce,
          postIds: [],
          taxonomies: {},
        };

        console.log('fetchMissingPostsAndTerms');

        let itemCount = 0;

        $('[data-pp-post-chooser], [data-pp-term-chooser]').each(function (index, el) {
          const container = $(this);
          const hiddenInput = $(this).find('.list-pills+input');
          const isPost = container.data('pp-post-chooser') != null;
          const isTerm = container.data('pp-term-chooser') != null;
          let taxonomySlug = null;
          if (isTerm) {
            taxonomySlug = container.data('pp-term-chooser').taxonomy;
          }

          const itemIds = $(hiddenInput).val().split(','); // .filter(parseInt);

          itemIds.forEach((itemId) => {
            itemId = parseInt(itemId);

            if (itemId <= 0) {
              // ...
            } else if (isPost && !isPostLoaded(itemId) && !ajaxRequest.postIds[itemId]) {
              ajaxRequest.postIds.push(itemId);
              ++itemCount;
            } else if (isTerm && !isTermLoaded(taxonomySlug, itemId)) {
              if (typeof ajaxRequest.taxonomies[taxonomySlug] === 'undefined') {
                ajaxRequest.taxonomies[taxonomySlug] = [];
              }

              if (!ajaxRequest.taxonomies[taxonomySlug].includes(itemId)) {
                ajaxRequest.taxonomies[taxonomySlug].push(itemId);
                ++itemCount;
              }
            } else {
              // ...
            }
          });
        });

        if (itemCount > 0) {
          // console.log(ajaxRequest);

          disablePostAndTermSearchers();

          $.post(pwplData.ajaxUrl, ajaxRequest)
            .done((responseData, status) => {
              storePostsAndTermsFromResponse(responseData);

              renderPostAndTermPills();
            })
            .always(() => {
              enablePostAndTermSearchers();
            });
        }
      };

      const disablePostAndTermSearchers = () => {
        $('[data-pp-post-chooser] input.search-posts, [data-pp-term-chooser]  input.search-terms').prop('disabled', true);
      };

      const enablePostAndTermSearchers = () => {
        $('[data-pp-post-chooser] input.search-posts, [data-pp-term-chooser]  input.search-terms').prop('disabled', false);
      };

      // const searchPostsAndTerms = (request, response) => {
      function searchPostsAndTerms(request, response) {
        console.log('searchPostsAndTerms');

        const controlId = $(this.element).attr('id');
        const container = $(this.element).closest('[data-pp-post-chooser], [data-pp-term-chooser]');
        const hiddenInput = $(this).find('.list-pills+input');
        const isPost = container.data('pp-post-chooser') != null;
        const isTerm = container.data('pp-term-chooser') != null;
        let taxonomySlug = null;
        let ajaxRequest = null;
        let args = null;
        if (isTerm) {
          taxonomySlug = container.data('pp-term-chooser').taxonomy;
        }

        if (isPost) {
          args = $(container).data('pp-post-chooser');

          console.log('postType');
          console.log(args.postType);

          ajaxRequest = {
            searchType: 'posts',
            postType: args.postType,
            searchQuery: request.term,
          };
        } else if (isTerm) {
          args = $(container).data('pp-term-chooser');

          ajaxRequest = {
            searchType: 'terms',
            taxonomy: args.taxonomy,
            searchQuery: request.term,
          };
        } else {
          // ...
        }

        if (ajaxRequest) {
          ajaxRequest.action = pwplData.searchPostOrTerms.action;
          ajaxRequest.nonce = pwplData.searchPostOrTerms.nonce;

          console.log(`Control Id: ${controlId}`);

          if (args.queryHandle) {
            ajaxRequest.queryHandle = args.queryHandle;
          }

          if (controlId) {
            ajaxRequest.controlId = controlId;
          }

          $.post(pwplData.ajaxUrl, ajaxRequest)
            .done((responseData, status) => {
              const suggestions = [];

              // console.log(responseData);

              storePostsAndTermsFromResponse(responseData);

              if (responseData && responseData.posts) {
                for (const itemId in responseData.posts) {
                  suggestions.push(getUniquePostOrTermTitle(responseData.posts[itemId]));
                }
              }

              if (responseData.taxonomies) {
                for (const taxonomySlug in responseData.taxonomies) {
                  let taxonomyTerms = responseData.taxonomies[taxonomySlug];
                  for (const itemId in taxonomyTerms) {
                    let termMeta = taxonomyTerms[itemId];

                    suggestions.push(getUniquePostOrTermTitle(termMeta));
                  }
                }
              }

              if (suggestions.length > 0) {
                response(suggestions);
              } else {
                response([]);
              }
            })
            .always(() => {
              console.log('Done');
            });
        }
      }

      const selectPostOrTerm = (event, ui) => {
        console.log('Test');
        console.log(event);

        const searchInput = $(event.target);
        const controlId = $(searchInput).attr('id');
        const container = $(searchInput).closest('[data-pp-post-chooser], [data-pp-term-chooser]');
        const hiddenInput = $(container).find('.list-pills+input');
        const isPost = container.data('pp-post-chooser') != null;
        const isTerm = container.data('pp-term-chooser') != null;
        let taxonomySlug = null;
        let ajaxRequest = null;
        let args = null;
        if (isTerm) {
          taxonomySlug = container.data('pp-term-chooser').taxonomy;
        }

        const uniqueTitle = ui.item.label;

        console.log(`Control ID: ${controlId}`);

        console.log(`select ${isPost ? 'Post' : taxonomySlug}: ${uniqueTitle} `);

        if (isPost) {
          const postMeta = findPostMeta(uniqueTitle);

          if (postMeta) {
            hiddenInput.val(hiddenInput.val() + ',' + postMeta.id);
            renderPostAndTermPills();
          }

          console.log(postMeta);
        } else if (isTerm) {
          if (taxonomySlug) {
            const termMeta = findTermMeta(taxonomySlug, uniqueTitle);
            if (termMeta) {
              hiddenInput.val(hiddenInput.val() + ',' + termMeta.id);
              renderPostAndTermPills();
            }
          }
        } else {
          // ...
        }

        setTimeout(function () {
          $(searchInput).val(' ');
        }, 250);
      };

      const renderPostAndTermPills = () => {
        // console.log('renderPostAndTermPills');

        $('[data-pp-post-chooser], [data-pp-term-chooser]').each(function (index, el) {
          const container = $(this);
          const hiddenInput = container.find('.list-pills+input');
          const pillsContainer = container.find('.list-pills');
          const isPost = container.data('pp-post-chooser') != null;
          const isTerm = container.data('pp-term-chooser') != null;
          let taxonomySlug = null;
          if (isTerm) {
            taxonomySlug = container.data('pp-term-chooser').taxonomy;
          }

          $(pillsContainer).empty();

          const itemIds = $(hiddenInput).val().split(','); //.filter(parseInt);
          const pillItemIds = [];

          // console.log( `Item ids: ${hiddenInput.val()}`) ;

          itemIds.forEach((itemId) => {
            let meta = null;
            itemId = parseInt(itemId);

            // console.log(`Try to create pill : ${itemId}`);

            if (itemId <= 0) {
              // ...
            } else if (isPost && isPostLoaded(itemId)) {
              meta = getPostMeta(itemId);
            } else if (isTerm && isTermLoaded(taxonomySlug, itemId)) {
              meta = getTermMeta(taxonomySlug, itemId);
            } else {
              // ...
            }

            if (!meta) {
              // ...
            } else if (pillItemIds.includes(itemId)) {
              // Already rendered.
            } else {
              const pill = $(`<div class="list-pill" data-item-id="${itemId}"><span>${getUniquePostOrTermTitle(meta)}</span></div>`);
              const removeButton = $(`<button><span class="dashicons dashicons-no-alt"></span></button>`);
              removeButton.click(function (event) {
                removePostOrTermPill($(this).closest('[data-item-id]'));
              });

              pill.append(removeButton);

              pillsContainer.append(pill);

              pillItemIds.push(itemId);
            }
          });

          $(hiddenInput).val(pillItemIds.join(','));
        });
      };

      const removePostOrTermPill = (pill) => {
        const container = $(pill).closest('[data-pp-post-chooser], [data-pp-term-chooser]');
        const pillsContainer = $(container).find('.list-pills');
        const hiddenInput = $(container).find('.list-pills+input');
        const itemId = $(pill).data('item-id');

        const newIds = $(hiddenInput).val().replace(itemId, '').split(',');
        $(hiddenInput).val(newIds.join(','));

        // $(this).closest('.post-pill').remove();
        pill.remove();

        // fetchMissingPosts();
      };

      const addQuickPopupMessage = (container, text) => {
        const popup = $(`<div class="pp-quick-popup-overlay"><div class="pp-quick-popup">${text}</div></div>`);
        $(container).append(popup);

        setTimeout(function () {
          $(popup).fadeOut(300, function () {
            $(this).remove();
          });
        }, pwplData.quickPopupTtl);
      };

      $('.cb-section').click(function (event) {
        const formRow = $(this).closest('.pp-form-row');
        const section = $(formRow).next();

        // section.slideToggle();
        section.fadeToggle();
      });

      $('.pp-select-panels')
        .prev('.pp-row')
        .find('select')
        .on('change', function (event) {
          const selectContainer = $(this).closest('.pp-row');
          const panelContainer = $(selectContainer).next('.pp-select-panels');
          const newValue = $(selectContainer).find('select').val();

          if (!newValue) {
            $(panelContainer).find('>*').slideUp();
          } else {
            $(panelContainer).find('>*').hide();
            $(panelContainer).find(`.panel-${newValue}`).fadeIn();
          }
        });

      if ($.isFunction($.fn.wpColorPicker)) {
        $('.pp-colour-chooser').wpColorPicker();
      }

      $('[data-pp-post-chooser] input.search-items, [data-pp-term-chooser] input.search-items').autocomplete({
        appendTo: $(event.target).closest('[data-pp-term-chooser], [data-pp-term-chooser]'),
        minLength: 3,
        source: searchPostsAndTerms,
        select: selectPostOrTerm,
      });

      $('[data-click-to-copy]').click(function (event) {
        event.preventDefault();

        const container = $(this);
        const args = $(container).data('click-to-copy');

        navigator.clipboard.writeText(args.textToCopy);

        addQuickPopupMessage(container, args.messageWhenCopied);
      });

      /**
       * Main entry point.
       */
      fetchMissingPostsAndTerms();

      /**
       * Old stuff
       **/
      if (false) {
        function searchPosts(request, response) {
          const container = $(this.element).closest('[data-pp-post-chooser]');
          const args = $(container).data('pp-post-chooser');

          const ajaxRequest = {
            id: container.prop('id'),
            action: pwplData.searchPosts.action,
            nonce: pwplData.searchPosts.nonce,
            postType: args.postType,
            searchQuery: request.term,
            queryType: 'text',
          };

          console.log(`Query: ${args.queryHandle}`);
          if (args.queryHandle) {
            ajaxRequest.queryHandle = args.queryHandle;
          }

          $.post(pwplData.ajaxUrl, ajaxRequest)
            .done((responseData, status) => {
              const suggestions = [];

              if (responseData && responseData.posts) {
                for (const postId in responseData.posts) {
                  const posts = responseData.posts[postId];

                  savePost(posts);

                  suggestions.push(getUniquePostTitle(posts));
                }
              }

              if (suggestions.length > 0) {
                response(suggestions);
              } else {
                response([]);
              }
            })
            .always(() => {
              console.log('Done');
            });
        }

        function selectSearchedPost(event, ui) {
          const term = ui.item.label;
          // console.log(`Commit: ${term}`);

          for (const postId in pwplData.posts) {
            const post = pwplData.posts[postId];

            if (term == getUniquePostTitle(post)) {
              // console.log(`Post ID = ${post.id}`);

              const searchInput = $(this);
              const container = $(this).closest('[data-pp-post-chooser]');
              const hiddenInput = $(container).find('.post-list-pills+input');

              // console.log(this);
              // console.log(container);
              // console.log(hiddenInput);

              const newIds = $(hiddenInput).val().split(',');
              newIds.push(post.id);
              $(hiddenInput).val(newIds.join(','));

              renderPosts();

              setTimeout(function () {
                $(searchInput).val(' ');
              }, 350);

              break;
            }
          }
        }

        const savePost = (post) => {
          if (post && post.id && !pwplData.posts[post.id.toString()]) {
            console.log(`Add post: ${post.title} (id = ${post.id})`);
            pwplData.posts[post.id.toString()] = post;
          }
        };

        const getUniquePostTitle = (post) => {
          return `${post.title} (${post.id})`;
        };

        function fetchMissingPosts() {
          var requestCount = 0;

          $('[data-pp-post-chooser]').each(function (index, el) {
            const missingPostIds = [];
            const container = $(this);
            const input = $(container).find('.list-pills+input');
            const postIds = $(input).val().split(',');
            const args = $(container).data('pp-post-chooser');

            postIds.forEach((postId) => {
              if (parseInt(postId) > 0 && !missingPostIds.includes(postId)) {
                missingPostIds.push(postId);
              }
            });

            if (missingPostIds.length) {
              ++requestCount;

              const ajaxRequest = {
                id: container.prop('id'),
                action: pwplData.searchPosts.action,
                nonce: pwplData.searchPosts.nonce,
                postType: args.postType,
                searchQuery: missingPostIds,
                queryType: 'id',
              };

              console.log(`Query: ${args.queryHandle}`);
              if (args.queryHandle) {
                ajaxRequest.queryHandle = args.queryHandle;
              }
              // console.log( 'INITIAL REQUEST' );
              // console.log( ajaxRequest);

              $.post(pwplData.ajaxUrl, ajaxRequest)
                .done((response) => {
                  console.log('done');
                  console.log(response);

                  if (response && response.posts) {
                    for (const postId in response.posts) {
                      savePost(response.posts[postId]);
                    }
                  }
                })
                .always(() => {
                  renderPosts();
                });
            }
          });

          if (requestCount == 0) {
            renderPosts();
          }
        }

        function renderPosts() {
          $('[data-pp-post-chooser]').each(function (index, el) {
            const container = $(this);
            const pillsContainer = $(container).find('.list-pills');
            const hiddenInput = $(container).find('.list-pills+input');
            const postIds = $(hiddenInput).val().split(',');

            postIds.forEach((postId) => {
              var post = null;

              console.log(`Check ${postId}`);

              if (postId && $(container).find(`[data-post-id="${postId}"]`).length == 0) {
                if (pwplData.posts[postId]) {
                  post = pwplData.posts[postId];
                } else {
                  post = {
                    id: postId,
                    title: `Unknown Post (${postId})`,
                  };
                }

                const pill = $(`<div class="list-pill" data-post-id="${post.id}"><span>${post.title}</span></div>`);
                const removeButton = $(`<button><span class="dashicons dashicons-no-alt"></span></button>`);

                removeButton.click(function (event) {
                  removePostPill($(this).closest('[data-post-id]'));
                });

                pill.append(removeButton);

                pillsContainer.append(pill);
              }
            });
          });
        }

        function removePostPill(pill) {
          const container = $(pill).closest('[data-pp-post-chooser]');
          const pillsContainer = $(container).find('.list-pills');
          const hiddenInput = $(container).find('.list-pills+input');

          // const pillsContainer = $(pill).closest('.post-list-pills');
          // const hiddenInput = pillsContainer.next();
          const postId = $(pill).data('post-id');

          // alert(`Remove: ${postId}`);

          const newIds = $(hiddenInput).val().replace(postId, '').split(',');
          $(hiddenInput).val(newIds.join(','));

          // $(this).closest('.post-pill').remove();
          pill.remove();

          fetchMissingPosts();
        }

        function addQuickPopupMessage(container, text) {
          const popup = $(`<div class="pp-quick-popup-overlay"><div class="pp-quick-popup">${text}</div></div>`);
          $(container).append(popup);

          setTimeout(function () {
            $(popup).fadeOut(300, function () {
              $(this).remove();
            });
          }, pwplData.quickPopupTtl);
        }
      }
    }
  });
})(jQuery);
