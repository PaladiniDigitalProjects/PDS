(function ($) {
	window.addEventListener("load", () => {
		const queryWrapper = document.querySelectorAll(".wp-block-query");
		queryWrapper.forEach((wrapper, index) => {
			const loadMoreButtonWrappers = $(wrapper).find(".ghub-query-load-more");
			const postTemplate = $(wrapper).find(".wp-block-post-template");
			const loadMoreButton = $(loadMoreButtonWrappers).find(
				".ghub_query_load_more_link"
			);
			const loadMoreButtonHtml = $(loadMoreButton).html();
			const loadingText = $(loadMoreButtonWrappers).data("loadingtext");
			const isAutoTrigger = $(loadMoreButtonWrappers).data("autotrigger");

			let currentPage = 1;
			function addNewPosts(buttonWrapper) {
				const url = new URL(location.href);
				const pagekey = $(buttonWrapper).data("pagekey");
				const totalPages = Number(buttonWrapper.get(0).dataset.totalpages);
				url.searchParams.set(pagekey, currentPage + 1);

				$(loadMoreButton).html(loadingText);
				if (currentPage < totalPages) {
					$.ajax({
						url: url.href,
						success: function (response) {
							const responseDocument = $($.parseHTML(response));
							const newPostsWrapper = responseDocument.find(
								`.wp-block-post-template`
							)[index];

							const newPosts = $(newPostsWrapper).find(`li`);
							postTemplate.append(...newPosts);
							currentPage++;
							$(loadMoreButton).html(loadMoreButtonHtml);
						},
					});
				}

				if (currentPage >= totalPages - 1) {
					$(loadMoreButtonWrappers).css({ display: "none" });
					$(loadMoreButton).html(loadMoreButtonHtml);
				}
			}

			if (isAutoTrigger === true) {
				async function autoLoad(entries) {
					entries.forEach(async (entry) => {
						if (entry.isIntersecting) {
							addNewPosts(entry.target);
						}
					});
				}

				const observer = new IntersectionObserver(autoLoad);
				if (loadMoreButtonWrappers[0]) {
					observer.observe(loadMoreButtonWrappers[0]);
				}
			} else {
				$(loadMoreButton).on("click", () => {
					addNewPosts(loadMoreButtonWrappers);
				});
			}
		});
	});
})(jQuery);
