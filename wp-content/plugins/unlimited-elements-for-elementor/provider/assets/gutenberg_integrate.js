jQuery(window).on("error", function (event) {
	var error = event.originalEvent;
	var details = error.message + ' (' + error.filename + ':' + error.lineno + ':' + error.colno + ')';

	if (error.error) {
		details = error.error.stack;
	}

	alert('JavaScript Error:\n\n' + details);
});

(function (wp) {
	var wbe = wp.blockEditor;
	var wc = wp.components;
	var we = wp.element;
	var el = we.createElement;

	var edit = function (props) {
		var blockProps = wbe.useBlockProps();
		var settingsLoadedState = we.useState(false);
		var settingsContentState = we.useState(null);
		var widgetContentState = we.useState(null);
		var ucSettingsState = we.useState(new UniteSettingsUC());
		var ucHelperState = we.useState(new UniteCreatorHelper());

		var attributes = props.attributes;

		var settingsId = "elementor-widget-" + blockProps.id;

		var settingsLoaded = settingsLoadedState[0];
		var setSettingsLoaded = settingsLoadedState[1];

		var settingsContent = settingsContentState[0];
		var setSettingsContent = settingsContentState[1];

		var widgetContent = widgetContentState[0];
		var setWidgetContent = widgetContentState[1];

		var ucSettings = ucSettingsState[0];
		var ucHelper = ucHelperState[0];

		var loadSettingsContent = function () {
			return g_ucAdmin.ajaxRequest("get_addon_settings_html", {
				id: attributes._id,
				config: attributes.data ? JSON.parse(attributes.data) : null,
			}, function (response) {
				var html = g_ucAdmin.getVal(response, "html");

				setSettingsContent(html);
			});
		};

		var loadWidgetContent = function () {
			return g_ucAdmin.ajaxRequest("get_addon_output_data", {
				id: attributes._id,
				settings: attributes.data ? JSON.parse(attributes.data) : null,
				selectors: true,
			}, function (response) {
				var html = g_ucAdmin.getVal(response, "html");
				var includes = g_ucAdmin.getVal(response, "includes");

				ucHelper.putIncludes(window, includes, function () {
					setWidgetContent(html);
				});
			});
		};

		var saveSettings = function () {
			props.setAttributes({ data: JSON.stringify(ucSettings.getSettingsValues()) });
		};

		we.useEffect(function () {
			if (props.isSelected === false) {
				setSettingsLoaded(false);
				setSettingsContent(null);

				return;
			}

			if (settingsLoaded === false) {
				loadSettingsContent();
				setSettingsLoaded(true);
			}
		}, [props.isSelected]);

		we.useEffect(function () {
			loadWidgetContent();
		}, [attributes.data]);

		we.useEffect(function () {
			if (settingsContent) {
				ucSettings.init(jQuery("#" + settingsId));
				ucSettings.setEventOnChange(saveSettings);
				ucSettings.setEventOnSelectorsChange(saveSettings);
			}
		}, [settingsContent]);

		var settingsElement = el(
			wbe.InspectorControls, {}, el(
				"div", { className: "block-editor-block-card" }, el(
					"div", { className: "block-editor-block-card__content" }, settingsContent
						? el("div", { id: settingsId, dangerouslySetInnerHTML: { __html: settingsContent } })
						: el(wc.Spinner),
				),
			),
		);

		var widgetElement = el(wc.Disabled, { dangerouslySetInnerHTML: { __html: widgetContent } });

		return el("div", blockProps, settingsElement, widgetElement);
	};

	for (var index in g_gutenbergBlocks) {
		var block = g_gutenbergBlocks[index];
		var args = jQuery.extend(block, { edit: edit });

		wp.blocks.registerBlockType(block.name, args);
	}
})(wp);
