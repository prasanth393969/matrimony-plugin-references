class figmaToElementor {
	constructor() {
		elementor.hooks.addAction("panel/open_editor/widget/eael-figma-to-elementor", this.initPanel.bind(this));
	}

	uploadFigmaImages = async (figmaUrls) => {
		try {
			const response = await fetch(localize.ajaxurl, {
				method: 'POST',
				body: new URLSearchParams({
					action: 'eael_upload_figma_images',
					nonce: localize.nonce,
					figma_urls: JSON.stringify(figmaUrls)
				})
			})

			if (!response.ok) {
				throw new Error('Network response was not ok');
			}

			const data = await response.json();
			if (!data.success) {
				throw new Error(data.data || 'Failed to upload images');
			}

			return data.data.images;
		} catch (error) {
			console.error('Error uploading images:', error);
			return null;
		}
	};

	insertElementorTemplate = async (elementId, response) => {
		if (response.content.length === 0) {
			return false;
		}

		const figmaUrls = this.findUrlsInContent(response.content);

		if (figmaUrls.length > 0) {
			const uploadedImages = await this.uploadFigmaImages(figmaUrls);

			// Replace Figma URLs with uploaded URLs in the content and add attachment IDs
			if (uploadedImages) {
				const uploadedImageKeys = Object.keys(uploadedImages);
				const replaceUrls = (obj) => {
					if (!obj || typeof obj !== 'object') return;

					for (const key in obj) {
						if (key.toLowerCase().includes('url') && typeof obj[key] === 'string' && obj[key].startsWith('http')) {

							const uploadedImage = uploadedImages[obj[key]];
							if (uploadedImage) {
								// Replace the URL
								obj[key] = uploadedImage.url;

								// Add attachment ID if the key contains 'image'
								// if (key.toLowerCase().includes('image')) {
								// 	const idKey = key.replace('url', 'id');
								// 	obj[idKey] = uploadedImage.id;
								// }
							}
						}
					}

					for (const key in obj) {
						if (Array.isArray(obj[key])) {
							obj[key].forEach(item => replaceUrls(item));
						} else if (typeof obj[key] === 'object') {
							replaceUrls(obj[key]);
						}
					}
				};

				response.content.forEach(item => replaceUrls(item));
			}
		}

		const templatelyElementor = window.elementor;

		if (typeof templatelyElementor !== 'undefined') {
			let insertIndex = window.TemplatelyIndex;
			const $e = parent.window.$e;

			if (typeof $e !== 'undefined') {
				for (let i = 0; i < response.content.length; i++) {
					$e.run('document/elements/create', {
						container: templatelyElementor.getPreviewContainer(),
						model: response.content[i],
						options: insertIndex >= 0 ? { at: insertIndex++ } : {},
					});
				}

				// Delete the figma widget after import
				try {
					const container = templatelyElementor.getPreviewView().getContainer();
					const targetElement = container.findChildrenRecursive(child => child.id === elementId);
					const deleteElement = targetElement?.parent?.children && targetElement.parent.children.length === 1 ? targetElement.parent : targetElement;

					if (targetElement) {
						$e.run('document/elements/delete', {
							container: deleteElement
						});
					}
				} catch (error) {
					console.log('catch')
					console.error('Error deleting element:', error);
				}

				// $e.run('document/elements/settings', {
				// 	container: templatelyElementor.getPreviewContainer(),
				// 	settings: {
				// 		template: response.page_settings.template,
				// 	},
				// });
			}

			return true;
		}

		return false;
	};

	findUrlsInContent = (content) => {
		const urls = [];

		const traverse = (obj) => {
			if (!obj || typeof obj !== 'object') return;

			// Check all properties for keys containing 'url'
			for (const key in obj) {
				if (key.toLowerCase().includes('url') && typeof obj[key] === 'string' && obj[key].startsWith('http')) {
					urls.push(obj[key]);
				}
			}

			// Recursively traverse through all properties
			for (const key in obj) {
				if (Array.isArray(obj[key])) {
					obj[key].forEach(item => traverse(item));
				} else if (typeof obj[key] === 'object') {
					traverse(obj[key]);
				}
			}
		};

		// Handle array of objects
		if (Array.isArray(content)) {
			content.forEach(item => traverse(item));
		} else {
			traverse(content);
		}

		return urls;
	};

	async initPanel(panel, model, view) {
		const elementId = view.container.args.id;
		panel.content.el.onclick = async (event) => {
			if (!event.target.dataset.event?.startsWith('eael:figmajson')) return;

			event.target.innerHTML = 'Importing...';
			event.target.disabled = true;

			let jsonData = null;

			try {
				if (event.target.dataset.event === "eael:figmajson:import") {
					const jsonString = panel.content.el.querySelector(".eael_figma_to_elementor_json").value;
					jsonData = JSON.parse(jsonString);
				} else if (event.target.dataset.event === "eael:figmajsonfile:import") {
					const fileId = model?.attributes?.settings?.attributes?.eael_figma_to_elementor_file?.id;

					if (!fileId) {
						throw new Error('Please upload a JSON file first.');
					}

					const response = await fetch(localize.ajaxurl, {
						method: 'POST',
						body: new URLSearchParams({
							action: 'eael_get_figma_file_content',
							nonce: localize.nonce,
							file_id: fileId
						})
					});

					if (!response.ok) {
						throw new Error('Network response was not ok');
					}

					const data = await response.json();
					if (!data.success) {
						throw new Error(data.data || 'Failed to load file content');
					}

					jsonData = JSON.parse(data.data);
				}

				if (!jsonData || !elementId) {
					throw new Error('Something went wrong! Please reload the page and try again.');
				}

				setTimeout(async () => {
					const result = await this.insertElementorTemplate(elementId, jsonData);
					if (result) {
						elementor.notifications.showToast({
							message: 'Figma content imported successfully!',
							type: 'success'
						});
					} else {
						throw new Error('Failed to import Figma content. Please try again.');
					}
				}, 10);
			} catch (error) {
				elementor.notifications.showToast({
					message: error.message,
					type: 'error',
					sticky: error.message.includes('reload the page')
				});
				event.target.innerHTML = 'Import';
				event.target.disabled = false;
			}
		};
	}

}

eael.hooks.addAction("editMode.init", "ea", () => {
	new figmaToElementor();
});
