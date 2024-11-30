/* eslint-disable @wordpress/no-unsafe-wp-apis */
/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import {
	SelectControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const addAttributes = (settings, name) => {
	if (name !== 'core/latest-comments') {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			...{
				postType: {
					type: 'string',
					default: '',
				},
			},
		},
	};
};

addFilter('blocks.registerBlockType', 'latestCommentsExtended/addAttributes', addAttributes);

const addInspectorControls = (BlockEdit) => (props) => {
	const { name, attributes, setAttributes } = props;
	const { postType } = attributes;

	if (name !== 'core/latest-comments') {
		return <BlockEdit {...props} />;
	}

	const postTypes = useSelect((select) => {
		const { getPostTypes } = select(coreStore);
		const excludedPostTypes = ['attachment'];
		const filteredPostTypes = getPostTypes({ per_page: -1 })?.filter(
			({ viewable, slug }) => viewable && !excludedPostTypes.includes(slug),
		);
		return filteredPostTypes;
	}, []);

	const postTypesSelectOptions = useMemo(
		() => [
			{ label: __('All', 'latest-comments-extended'), value: '' },
			...(postTypes || []).map(({ labels, slug }) => ({
				label: labels.singular_name,
				value: slug,
			})),
		],
		[postTypes],
	);

	const postTypeControlLabel = __('Post type');
	const postTypeControlHelp = __(
		'Select the content type to filter comments by.',
		'latest-comments-extended',
	);

	const setPostType = (newValue) => setAttributes({ postType: newValue });

	return (
		<>
			<BlockEdit {...props} />
			<InspectorControls>
				<ToolsPanel
					label={__('Filters')}
					resetAll={() => {
						setPostType('');
					}}
				>
					<ToolsPanelItem
						hasValue={() => !!postType}
						label={postTypeControlLabel}
						onDeselect={() => setPostType('')}
					>
						{postTypesSelectOptions.length > 2 ? (
							<SelectControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								options={postTypesSelectOptions}
								value={postType}
								label={postTypeControlLabel}
								onChange={setPostType}
								help={postTypeControlHelp}
							/>
						) : (
							<ToggleGroupControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								isBlock
								value={postType}
								label={postTypeControlLabel}
								onChange={setPostType}
								help={postTypeControlHelp}
							>
								{postTypesSelectOptions.map((option) => (
									<ToggleGroupControlOption
										key={option.value}
										value={option.value}
										label={option.label}
									/>
								))}
							</ToggleGroupControl>
						)}
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>
		</>
	);
};

addFilter('editor.BlockEdit', 'latestCommentsExtended/addInspectorControls', addInspectorControls);
