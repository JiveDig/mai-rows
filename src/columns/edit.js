/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps, useInnerBlocksProps, BlockControls, JustifyContentControl, BlockVerticalAlignmentToolbar } from '@wordpress/block-editor';
import { Panel, PanelBody, PanelRow, BaseControl, TextControl, FormTokenField } from '@wordpress/components';
import MaiMultiSelectDuplicate from './select-duplicates';
// import { close, plus, settings, justifyCenter, justifyLeft, justifyRight } from "@wordpress/icons";

// TODO: https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/columns/edit.js

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { justifyContent, alignItems, columnsXl, columnsLg, columnsMd, columnsSm } = attributes;
	// const blockProps       = useBlockProps( { className: 'jivedig-columns' } );

	const options = [
		{
			value: 'auto',
			label: __( 'Equal Widths' ),
		},
		{
			value: '1/4',
			label: __( '25%' ),
		},
		{
			value: '1/3',
			label: __( '33%' ),
		},
		{
			value: '1/2',
			label: __( '50%' ),
		},
		{
			value: '2/3',
			label: __( '66%' ),
		},
		{
			value: '3/4',
			label: __( '75%' ),
		},
		{
			value: '1/1',
			label: __( '100%' ),
		},
		{
			value: 'fit',
			label: __( 'Fit Content' ),
		},
		{
			value: 'fill',
			label: __( 'Fill Space' ),
		},
	];

	const mapValuesToLabels = ( values ) => {
		return values.map( value => {
			const option = options.find( opt => opt.value === value );
			return option ? option.label : value;
		});
	}

	const mapLabelsToValues = ( values ) => {
		return values.map( value => {
			const option = options.find( opt => opt.label === value );
			return option ? option.value : value;
		});
	}

	const blockProps       = useBlockProps( { className: 'jivedig-columns' } );
	const innerBlocksProps = useInnerBlocksProps(
		blockProps,
		{
			allowedBlocks: [ 'mai/column' ],
			orientation: 'horizontal',
			template: [
				[ 'mai/column' ],
				[ 'mai/column' ],
			]
		}
	);

	return (
		<>
			<BlockControls group="block">
				<JustifyContentControl
					value={ justifyContent }
					onChange={ ( value ) => {
						setAttributes( { justifyContent: value } );
					} }
				/>
				<BlockVerticalAlignmentToolbar
					value={ alignItems }
					onChange={ ( value ) => {
						setAttributes( { alignItems: value } );
					}}
				/>
			</BlockControls>
			<InspectorControls key="Columns">
				<PanelBody>
					<h2>{ __( 'Column Width(s)' ) }</h2>
					<BaseControl
						help={ __( 'Custom arrangements will repeat in the sequence you set here. Only set one value if you want all columns to be the same size.' ) }
					></BaseControl>
					<BaseControl label={ __( 'Desktop' ) }>
						<MaiMultiSelectDuplicate
							key="columnsXl"
							options={ options }
							value={ mapValuesToLabels( columnsXl ) }
							onChange={ ( values ) => {
								setAttributes( { columnsXl: mapLabelsToValues( values ) } );
							}}
							onCreateOption={ ( value ) => {
								setAttributes( { columnsXl: [ ...columnsXl, value ] } );
							}}
						/>
					</BaseControl>
					<BaseControl label={ __( 'Large Tablet' ) }>
						<MaiMultiSelectDuplicate
							key="columnsLg"
							options={ options }
							value={ mapValuesToLabels( columnsLg ) }
							onChange={ ( values ) => {
								setAttributes( { columnsLg: mapLabelsToValues( values ) } );
							}}
							onCreateOption={ ( value ) => {
								setAttributes( { columnsLg: [ ...columnsLg, value ] } );
							}}
						/>
					</BaseControl>
					<BaseControl label={ __( 'Small Tablet' ) }>
						<MaiMultiSelectDuplicate
							key="columnsMd"
							options={ options }
							value={ mapValuesToLabels( columnsMd ) }
							onChange={ ( values ) => {
								setAttributes( { columnsMd: mapLabelsToValues( values ) } );
							}}
							onCreateOption={ ( value ) => {
								setAttributes( { columnsMd: [ ...columnsMd, value ] } );
							}}
						/>
					</BaseControl>
					<BaseControl
						label={ __( 'Mobile' ) }
						// help={ __( 'Custom arrangements will repeat in the sequence you set here. Only set one value if you want all columns to be the same size.' ) }
					>
						<MaiMultiSelectDuplicate
							key="columnsSm"
							options={ options }
							value={ mapValuesToLabels( columnsSm ) }
							onChange={ ( values ) => {
								setAttributes( { columnsSm: mapLabelsToValues( values ) } );
							}}
							onCreateOption={ ( value ) => {
								setAttributes( { columnsSm: [ ...columnsSm, value ] } );
							}}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div { ...innerBlocksProps } />
		</>
	);
}
