<?php
namespace Essential_Addons_Elementor\Pro\Extensions;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Essential_Addons_Elementor\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Custom_Cursor {

	/**
	 * Initialize hooks
	 */
	public function __construct() {
		add_action('elementor/documents/register_controls', [$this, 'document_register_controls'], 10);
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/frontend/before_render', [ $this, 'before_render' ], 100 );
		add_action( 'eael/custom_cursor/page_render', [ $this, 'render_custom_cursor_html' ], 10, 2 );
		add_action( 'eael/register_custom_cursor_assets', [ $this, 'register_assets' ] );
		add_filter( 'eael/extentions/global_settings', [ $this, 'global_settings' ], 10, 3 );
	}

	private function get_trail_effect_icon( $effect_name ) {
		$icons = [
			'following_dots' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6.41667 7C6.41667 7.15471 6.47812 7.30308 6.58752 7.41248C6.69692 7.52187 6.84529 7.58333 7 7.58333C7.15471 7.58333 7.30308 7.52187 7.41248 7.41248C7.52187 7.30308 7.58333 7.15471 7.58333 7C7.58333 6.84529 7.52187 6.69692 7.41248 6.58752C7.30308 6.47812 7.15471 6.41667 7 6.41667C6.84529 6.41667 6.69692 6.47812 6.58752 6.58752C6.47812 6.69692 6.41667 6.84529 6.41667 7Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M1.75 7C1.75 7.68944 1.8858 8.37213 2.14963 9.00909C2.41347 9.64605 2.80018 10.2248 3.28769 10.7123C3.7752 11.1998 4.35395 11.5865 4.99091 11.8504C5.62787 12.1142 6.31056 12.25 7 12.25C7.68944 12.25 8.37213 12.1142 9.00909 11.8504C9.64605 11.5865 10.2248 11.1998 10.7123 10.7123C11.1998 10.2248 11.5865 9.64605 11.8504 9.00909C12.1142 8.37213 12.25 7.68944 12.25 7C12.25 6.31056 12.1142 5.62787 11.8504 4.99091C11.5865 4.35395 11.1998 3.7752 10.7123 3.28769C10.2248 2.80018 9.64605 2.41347 9.00909 2.14963C8.37213 1.8858 7.68944 1.75 7 1.75C6.31056 1.75 5.62787 1.8858 4.99091 2.14963C4.35395 2.41347 3.7752 2.80018 3.28769 3.28769C2.80018 3.7752 2.41347 4.35395 2.14963 4.99091C1.8858 5.62787 1.75 6.31056 1.75 7Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>',
			'phantomsmoke' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6.27154 3.96423C6.40988 4.04421 6.45712 4.22184 6.37701 4.36088C6.29689 4.4999 5.89666 4.87394 5.7583 4.79392C5.62 4.71387 5.79597 4.21007 5.87606 4.07105C5.95615 3.93201 6.13319 3.88416 6.27154 3.96423Z" fill="#515962" stroke="#515962"/>
					<path d="M7.98041 4.00407C7.84207 4.08405 7.79483 4.26168 7.87495 4.40072C7.95506 4.53974 8.3553 4.91378 8.49366 4.83376C8.63196 4.75371 8.45599 4.24991 8.37589 4.11089C8.2958 3.97185 8.11877 3.924 7.98041 4.00407Z" fill="#515962" stroke="#515962"/>
					<path d="M12.9465 12.2519C12.9765 12.4398 13.0261 12.6732 12.8668 12.7773C12.7913 12.8267 12.6922 12.8377 12.5977 12.7899C12.2696 12.6241 11.8978 12.4362 11.1588 12.347C10.9567 12.3226 10.7726 12.3107 10.5959 12.3107C10.1815 12.3107 9.89333 12.3772 9.63918 12.4359C9.42118 12.4862 9.23293 12.5297 8.95805 12.5297C8.8311 12.5297 8.69502 12.5207 8.54215 12.5023C8.01113 12.4382 7.7844 12.3234 7.49739 12.178C7.16646 12.0103 6.79132 11.8204 6.04609 11.7304C5.84391 11.706 5.65998 11.6942 5.48362 11.6942C5.06956 11.6942 4.78265 11.7607 4.52947 11.8195C4.31302 11.8697 4.12603 11.9131 3.85232 11.9131C3.72561 11.9131 3.58968 11.9041 3.43676 11.8857C2.87021 11.8173 2.48401 11.6793 2.08926 11.5332C1.59829 11.3516 1.20469 10.9559 1.0599 10.4711C0.981263 10.2079 1.04083 9.92958 1.09423 9.6601C1.19386 9.15735 1.19289 8.75907 1.08786 8.22668C1.03687 7.96818 0.981327 7.70085 1.05689 7.44844C1.23608 6.84985 1.8165 6.42883 2.48134 6.42883H3.2322C3.5691 6.42883 3.81476 6.12051 3.81476 5.78529V4.91475C3.81476 3.18062 5.24694 1.76978 6.99345 1.76978C8.74005 1.76978 10.1722 3.18062 10.1722 4.91475V5.78529C10.1722 6.12051 10.4504 6.42883 10.7874 6.42883H11.5382C12.3442 6.42883 13 7.05986 13 7.8573C12.8293 9.53924 12.6751 10.5498 12.9465 12.2519Z" stroke="#515962" stroke-linejoin="round"/>
					<path d="M8.58683 6.56977C8.18557 6.82314 7.71019 6.96977 7.20055 6.96977C6.69092 6.96977 6.21553 6.82314 5.81428 6.56977" stroke="#515962" stroke-linecap="round"/>
				</svg>',
			'spiritecho' => '<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clip-path="url(#clip0_5648_343)">
					<path d="M6.33203 5.25H6.33786" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M8.66797 5.25H8.6738" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M7.5013 1.75058C8.58427 1.75058 9.62288 2.18079 10.3887 2.94656C11.1544 3.71234 11.5846 4.75095 11.5846 5.83392V6.41725H12.168C12.4774 6.41725 12.7741 6.54017 12.9929 6.75896C13.2117 6.97775 13.3346 7.2745 13.3346 7.58392C13.3346 7.89334 13.2117 8.19008 12.9929 8.40887C12.7741 8.62767 12.4774 8.75058 12.168 8.75058H11.5846V10.5006L12.7513 12.2506H6.91797C6.01201 12.2512 5.1411 11.9005 4.48837 11.2723C3.83564 10.644 3.45194 9.78715 3.41797 8.88183V8.75H2.83464C2.52522 8.75 2.22847 8.62708 2.00968 8.40829C1.79089 8.1895 1.66797 7.89275 1.66797 7.58333C1.66797 7.27391 1.79089 6.97717 2.00968 6.75838C2.22847 6.53958 2.52522 6.41667 2.83464 6.41667H3.41797V5.83333C3.41797 4.75037 3.84818 3.71175 4.61395 2.94598C5.37972 2.18021 6.41834 1.75058 7.5013 1.75058Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M6.91797 8.16732H8.08464C8.08464 8.01261 8.02318 7.86424 7.91378 7.75484C7.80438 7.64544 7.65601 7.58398 7.5013 7.58398C7.34659 7.58398 7.19822 7.64544 7.08882 7.75484C6.97943 7.86424 6.91797 8.01261 6.91797 8.16732Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					</g>
					<defs>
					<clipPath id="clip0_5648_343">
					<rect width="14" height="14" fill="white" transform="translate(0.5)"/>
					</clipPath>
					</defs>
				</svg>',
			'frostsparkles' => '<svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6.33431 2.33268L7.50098 2.91602L8.66764 2.33268M7.50098 1.16602V4.95768L9.25098 5.96102M10.9588 3.65572L11.037 4.95772L12.1255 5.67639M12.5528 4.08268L9.26921 5.97852L9.27504 7.99568M12.1255 8.32239L11.037 9.04105L10.9588 10.3431M12.5527 9.91602L9.26907 8.02018L7.5249 9.03402M8.66764 11.666L7.50098 11.0827L6.33431 11.666M7.50098 12.8326V9.04089L5.75098 8.03756M4.04313 10.3431L3.96496 9.04105L2.87646 8.32239M2.44922 9.91601L5.7328 8.02018L5.72697 6.00301M2.87646 5.67639L3.96496 4.95772L4.04313 3.65572M2.44922 4.08268L5.7328 5.97852L7.47697 4.96468" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>',
			'trailparticles' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3 6.5H3.00583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11 6.5H11.0117" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M13 9H13.0117" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5.5 5.5C5.5 5.63261 5.44732 5.75979 5.35355 5.85355C5.25979 5.94732 5.13261 6 5 6C4.86739 6 4.74021 5.94732 4.64645 5.85355C4.55268 5.75979 4.5 5.63261 4.5 5.5C4.5 5.36739 4.55268 5.24021 4.64645 5.14645C4.74021 5.05268 4.86739 5 5 5C5.13261 5 5.25979 5.05268 5.35355 5.14645C5.44732 5.24021 5.5 5.36739 5.5 5.5Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M10 8.5C10 8.63261 9.94732 8.75979 9.85355 8.85355C9.75979 8.94732 9.63261 9 9.5 9C9.36739 9 9.24021 8.94732 9.14645 8.85355C9.05268 8.75979 9 8.63261 9 8.5C9 8.36739 9.05268 8.24021 9.14645 8.14645C9.24021 8.05268 9.36739 8 9.5 8C9.63261 8 9.75979 8.05268 9.85355 8.14645C9.94732 8.24021 10 8.36739 10 8.5Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5.25 11.25H5.26167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5 8.5C5 8.63261 4.94732 8.75979 4.85355 8.85355C4.75979 8.94732 4.63261 9 4.5 9C4.36739 9 4.24021 8.94732 4.14645 8.85355C4.05268 8.75979 4 8.63261 4 8.5C4 8.36739 4.05268 8.24021 4.14645 8.14645C4.24021 8.05268 4.36739 8 4.5 8C4.63261 8 4.75979 8.05268 4.85355 8.14645C4.94732 8.24021 5 8.36739 5 8.5Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M2.5 2H2.50583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5 3H5.00583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11 10H11.0058" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8.80078 5.25H8.80661" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M3 4H3.01167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7 4H7.01167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7.25 7.25H7.26167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8.25 10.5C8.25 10.6326 8.19732 10.7598 8.10355 10.8536C8.00979 10.9473 7.88261 11 7.75 11C7.61739 11 7.49021 10.9473 7.39645 10.8536C7.30268 10.7598 7.25 10.6326 7.25 10.5C7.25 10.3674 7.30268 10.2402 7.39645 10.1464C7.49021 10.0527 7.61739 10 7.75 10C7.88261 10 8.00979 10.0527 8.10355 10.1464C8.19732 10.2402 8.25 10.3674 8.25 10.5Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>',
			'inktrail' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.6 12.1093C2.94732 12.1923 3.26246 12.2637 3.60746 12.3287C4.29045 12.4573 4.98521 12.5531 5.69642 12.5572C6.39581 12.5259 7.20544 12.5784 7.88682 11.8236C8.50331 10.9008 7.96267 10.0184 7.63497 9.41589C7.25228 8.78037 6.79413 8.23289 6.32245 7.71165C6.32229 7.71148 6.32214 7.71131 6.32199 7.71114C6.12757 7.49686 5.96421 7.28803 5.87097 7.0987C5.82215 7.00155 5.81211 6.92701 5.81487 6.92044C5.81996 6.91955 5.79027 6.9492 5.83645 6.90867C6.07874 6.73541 6.76295 6.6609 7.32528 6.62207C7.91575 6.5793 8.52589 6.55969 9.15833 6.48006C9.47418 6.43815 9.79962 6.38537 10.1443 6.26527C10.4665 6.14741 10.9406 5.93131 11.1219 5.38317C11.2781 4.88888 11.3748 4.29762 11.199 3.70831C11.0243 3.1172 10.6292 2.68973 10.2848 2.34841C10.0298 2.09797 9.78134 1.87531 9.55316 1.64645C9.45939 1.55268 9.33222 1.5 9.19961 1.5C9.067 1.5 8.93982 1.55268 8.84605 1.64645C8.75229 1.74021 8.69961 1.86739 8.69961 2C8.69961 2.13261 8.75229 2.25979 8.84605 2.35355C9.09726 2.6051 9.35367 2.83506 9.58274 3.06055C10.2579 3.68575 10.4436 4.25846 10.1683 5.08204C10.1992 5.20091 9.57001 5.44162 9.0308 5.48823C8.46666 5.55987 7.86672 5.58082 7.25389 5.62462C6.63163 5.69911 6.03553 5.62768 5.24226 6.10435C5.04802 6.23395 4.82946 6.54244 4.81806 6.84063C4.80002 7.14325 4.88293 7.34494 4.97173 7.53616C5.1496 7.88881 5.35384 8.12816 5.58219 8.38398C5.58237 8.38417 5.58255 8.38437 5.58273 8.38457C6.03477 8.88304 6.4754 9.3904 6.8297 9.9207C7.1798 10.4363 7.44166 11.0559 7.2345 11.3574C6.63141 12.1024 4.92717 12.0959 3.62665 12.1224C3.27889 12.1225 2.95693 12.1199 2.6 12.1093Z" fill="#515962"/>
</svg>',
			'glowingboxes' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect width="2" height="2" transform="matrix(-1 0 0 1 11.5 2.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 10.5 0.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 9.5 3.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 7.5 4.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 5.5 5.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 4.5 7.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 5.5 9.5)" stroke="#515962"/>
							<rect width="2" height="2" transform="matrix(-1 0 0 1 6.5 11.5)" stroke="#515962"/>
						</svg>',
			'colorballs' => '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11 5.8H11.0117" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7 3.5H7.00583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8.79102 5.60416C8.79102 5.73676 8.73834 5.86394 8.64457 5.95771C8.5508 6.05148 8.42362 6.10416 8.29102 6.10416C8.15841 6.10416 8.03123 6.05148 7.93746 5.95771C7.84369 5.86394 7.79102 5.73676 7.79102 5.60416C7.79102 5.47155 7.84369 5.34437 7.93746 5.2506C8.03123 5.15683 8.15841 5.10416 8.29102 5.10416C8.42362 5.10416 8.5508 5.15683 8.64457 5.2506C8.73834 5.34437 8.79102 5.47155 8.79102 5.60416Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9.5 2.75H9.51167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7 8H7.00583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5.5 10.8125H5.51167" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M2.5 10.5H2.50583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8.25 10H8.25583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M1.25 8H1.25583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9.5 8H9.50583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12 3.25H12.0058" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M11.207 10.1875H11.2187" stroke="#515962" stroke-width="1.41421" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12.2695 8H12.2754" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M4.5 3.25C4.5 3.38261 4.44732 3.50979 4.35355 3.60355C4.25979 3.69732 4.13261 3.75 4 3.75C3.86739 3.75 3.74021 3.69732 3.64645 3.60355C3.55268 3.50979 3.5 3.38261 3.5 3.25C3.5 3.11739 3.55268 2.99021 3.64645 2.89645C3.74021 2.80268 3.86739 2.75 4 2.75C4.13261 2.75 4.25979 2.80268 4.35355 2.89645C4.44732 2.99021 4.5 3.11739 4.5 3.25Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M1.5 3.75H1.50583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5.5 5.5H5.50583" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M4.5 8C4.5 8.13261 4.44732 8.25979 4.35355 8.35355C4.25979 8.44732 4.13261 8.5 4 8.5C3.86739 8.5 3.74021 8.44732 3.64645 8.35355C3.55268 8.25979 3.5 8.13261 3.5 8C3.5 7.86739 3.55268 7.74021 3.64645 7.64645C3.74021 7.55268 3.86739 7.5 4 7.5C4.13261 7.5 4.25979 7.55268 4.35355 7.64645C4.44732 7.74021 4.5 7.86739 4.5 8Z" stroke="#515962" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M2.40039 5.8H2.41206" stroke="#515962" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>'
		];

		return $icons[ $effect_name ] ?? '';
	}
	public function document_register_controls( $element ) {

		if ( Helper::prevent_extension_loading(get_the_ID())) {
            return;
        }

        $global_settings = get_option('eael_global_settings');
        
        $element->start_controls_section(
            'eael_ext_custom_cursor_section',
            [
                'label' => __('<i class="eaicon-logo"></i> Custom Cursor', 'essential-addons-for-elementor-lite'),
                'tab' => Controls_Manager::TAB_SETTINGS,
            ]
        );

		$element->add_control(
			'eael_enable_custom_cursor',
			[
				'label'        => __( 'Enable Custom Cursor', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes'
			]
		);

		$element->add_control(
			'eael_custom_cursor_apply_changes_notice',
			[
				'type' => Controls_Manager::NOTICE,
				'notice_type' => 'warning',
				'dismissible' => false,
				'content' => esc_html__( "Please click the 'Apply Changes' button below to view the changes in the editor.", 'essential-addons-elementor' ),
			]
		);

        $element->add_control(
            'eael_ext_custom_cursor_has_global',
            [
                'label' => __('Enabled Globally?', 'essential-addons-for-elementor-lite'),
                'type' => Controls_Manager::HIDDEN,
                'default' => (isset($global_settings['eael_ext_custom_cursor']['enabled']) ? $global_settings['eael_ext_custom_cursor']['enabled'] : false),
            ]
        );

        if (isset($global_settings['eael_ext_custom_cursor']['enabled']) && ($global_settings['eael_ext_custom_cursor']['enabled'] == true) && get_the_ID() != $global_settings['eael_ext_custom_cursor']['post_id'] && get_post_status($global_settings['eael_ext_custom_cursor']['post_id']) == 'publish') {
            $element->add_control(
                'eael_ext_custom_cursor_global_warning_text',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => __('You can modify the Global Custom Cursor by <strong><a href="' . get_bloginfo('url') . '/wp-admin/post.php?post=' . $global_settings['eael_ext_custom_cursor']['post_id'] . '&action=elementor">Clicking Here</a></strong>', 'essential-addons-for-elementor-lite'),
                    'content_classes' => 'eael-warning',
                    'separator' => 'before',
					'conditions' => [
						'relation' => 'or',
						'terms' => [
							[
								'name' => 'eael_enable_custom_cursor',
								'operator' => '===',
								'value' => 'yes'
							],
							[
								'name' => 'eael_cursor_trail_show',
								'operator' => '===',
								'value' => 'yes'
							]
						]
					]
                ]
            );
        } else {
            $element->add_control(
                'eael_enable_custom_cursor_global',
                [
                    'label' => __('Enable Globally', 'essential-addons-for-elementor-lite'),
                    'type' => Controls_Manager::SWITCHER,
                    'default' => 'no',
                    'label_on' => __('Yes', 'essential-addons-for-elementor-lite'),
                    'label_off' => __('No', 'essential-addons-for-elementor-lite'),
                    'return_value' => 'yes',
                    'separator' => 'before',
                    'conditions' => [
						'relation' => 'or',
						'terms' => [
							[
								'name' => 'eael_enable_custom_cursor',
								'operator' => '===',
								'value' => 'yes'
							],
							[
								'name' => 'eael_cursor_trail_show',
								'operator' => '===',
								'value' => 'yes'
							]
						]
					]
                ]
            );

			$element->add_control(
				'eael_ext_custom_cursor_global_alert',
				[
					'type' => Controls_Manager::ALERT,
					'alert_type' => 'info',
					'heading' => '',
					'content' => __('Enabling this option will affect the entire site.', 'essential-addons-for-elementor-lite'),
                    'conditions' => [
						'relation' => 'or',
						'terms' => [
							[
								'name' => 'eael_enable_custom_cursor',
								'operator' => '===',
								'value' => 'yes'
							],
							[
								'name' => 'eael_cursor_trail_show',
								'operator' => '===',
								'value' => 'yes'
							]
						]
					]
				]
			);

            $element->add_control(
                'eael_ext_custom_cursor_global_display_condition',
                [
                    'label' => __('Display On', 'essential-addons-for-elementor-lite'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'all',
                    'options' => [
                        'posts' => __('All Posts', 'essential-addons-for-elementor-lite'),
                        'pages' => __('All Pages', 'essential-addons-for-elementor-lite'),
                        'all' => __('All Posts & Pages', 'essential-addons-for-elementor-lite'),
                    ],
                    'condition' => [
                        'eael_enable_custom_cursor' => 'yes',
                        'eael_enable_custom_cursor_global' => 'yes',
                    ],
					'conditions' => [
						'relation' => 'or',
						'terms' => [
							[
								'terms' => [
									[
										'name' => 'eael_enable_custom_cursor',
										'operator' => '===',
										'value' => 'yes'
									],
									[
										'name' => 'eael_enable_custom_cursor_global',
										'operator' => '===',
										'value' => 'yes'
									]
								]
							],
							[
								'terms' => [
									[
										'name' => 'eael_cursor_trail_show',
										'operator' => '===',
										'value' => 'yes'
									],
									
								]
							] 
						]
					],
                    'separator' => 'before',
                ]
            );
        }

		$this->register_controls( $element, false );
	}

	public function register_controls( $element, $section_start = true ) {

		if ( $section_start ) {
			$element->start_controls_section(
				'eael_custom_cursor_section',
				[
					'label' => __( '<i class="eaicon-logo"></i> Custom Cursor', 'essential-addons-elementor' ),
					'tab'   => Controls_Manager::TAB_ADVANCED
				]
			);

			$element->add_control(
				'eael_enable_custom_cursor',
				[
					'label'        => __( 'Enable Custom Cursor', 'essential-addons-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes'
				]
			);
		}

        $element->add_control(
            'eael_icon_to_svg_path',
            [
                'type' => Controls_Manager::HIDDEN,
                'default' => EAEL_PLUGIN_URL . "assets/front-end/js/lib-view/icons/"
            ]
        );

		$element->start_controls_tabs(
			'eael_custom_cursor_tabs',
			[
				'condition' => [
					'eael_enable_custom_cursor' => 'yes'
				]
			]
		);

		$element->start_controls_tab(
			'eael_custom_cursor_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'essential-addons-elementor' ),
			]
		);

		$this->custom_cursor_controllers( $element );

		$element->end_controls_tab();

		$element->start_controls_tab(
			'eael_custom_cursor_tab_pointer',
			[
				'label' => esc_html__( 'Pointer', 'essential-addons-elementor' ),
			]
		);

		$element->add_control(
			'eael_pointer_selectors',
			[
				'label'     => '',
				'type'      => Controls_Manager::HIDDEN,
				'default'   => [ 
					'a[href]',
					'button',
					'input[type="button"]',
					'input[type="submit"]',
					'input[type="reset"]',
					'input[type="checkbox"]',
					'input[type="radio"]',
					'label',
					'select',
					'summary' 
				]
			]
		);

		$element->add_control(
			'eael_enable_pointer_cursor',
			[
				'label'        => esc_html__( 'Enable For Pointer Elements', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->custom_cursor_controllers( $element, '_pointer' );

		$element->end_controls_tab();

		$element->end_controls_tabs();

		if( !$section_start ) {
			$element->add_control(
				'eael_custom_cursor_apply_changes_2',
				[
					'label'     => '',
					'show_label' => false,
					'label_block' => true,
					'type'      => Controls_Manager::BUTTON,
					'button_type' => 'danger',
					'text'      => __( 'Apply Changes', 'essential-addons-elementor' ),
					'event'     => 'eael:custom_cursor:apply_changes',
					'condition' => [
						'eael_enable_custom_cursor' => 'yes'
					]
				]
			);
		}

		$element->add_control(
			'eael_cursor_trail_heading',
			[
				'label'     => __( 'Trail Animation', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_control(
			'eael_cursor_trail_show',
			[
				'label'     => __( 'Enable Trail', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			]
		);

		$element->add_control(
			'eael_cursor_trail_hide_on_idle',
			[
				'label'       => __( 'Hide on Idle', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_block' => false,
				'ai'          => [ 'active' => false ],
				'default'     => 'no',
				'return_value' => 'yes',
				'condition'   => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => [ 'following_dots', 'spiritecho', 'phantomsmoke' ]
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_idle_timeout',
			[
				'label'     => __( 'Idle Timeout (s)', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 60
					]
				],
				'default'   => [
					'size' => 3,
					'unit' => 'px'
				],
				'separator'   => 'after',
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_hide_on_idle' => 'yes',
					'eael_cursor_trail_effect' => [ 'following_dots', 'spiritecho', 'phantomsmoke' ]
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_effect',
			[
				'label'       => __( 'Effect', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => true,
				'options'     => [
					'inktrail' => [
						'title' => __( 'Ink Trail', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'inktrail' ) . __( 'Ink Trail', 'essential-addons-elementor' )
					],
					'trailparticles' => [
						'title' => __( 'Trail Particles', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'trailparticles' ) . __( 'Trail Particles', 'essential-addons-elementor' )
					],
					'phantomsmoke' => [
						'title' => __( 'Phantom Smoke', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'phantomsmoke' ) . __( 'Phantom Smoke', 'essential-addons-elementor' )
					],
					'spiritecho' => [
						'title' => __( 'Spirit Echo', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'spiritecho' ) . __( 'Spirit Echo', 'essential-addons-elementor' )
					],
					'glowingBoxes' => [
						'title' => __( 'Glow Blocks', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'glowingboxes' ) . __( 'Glow Blocks', 'essential-addons-elementor' )
					],
					'colorBalls' => [
						'title' => __( 'Chroma Orbs', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'colorballs' ) . __( 'Chroma Orbs', 'essential-addons-elementor' )
					],
					'frostsparkles' => [
						'title' => __( 'Frost Sparkles', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'frostsparkles' ) . __( 'Frost Sparkles', 'essential-addons-elementor' )
					],
					'following_dots'        => [
						'title' => __( 'Dot Comet', 'essential-addons-elementor' ),
						'text'  => $this->get_trail_effect_icon( 'following_dots' ) . __( 'Dot Comet', 'essential-addons-elementor' )
					],
				],
				'default'   => 'inktrail',
				'multiline' => true,
				'condition' => [
					'eael_cursor_trail_show' => 'yes'
				]	
			]
		);

		// Following Dots Trail Controllers
		$this->following_dots_trail_controllers( $element );
		
		// Ghost Following Controllers
		$this->phantomsmoke_controllers( $element );

		// Ghost Following Cursor Controllers
		$this->ghost_following_controllers( $element );

		// // Snowflake Cursor Controllers
		$this->snowflake_cursor_controllers( $element );

		// // Ink Line Controllers
		$this->ink_line_controllers( $element );

		// // Glowing Boxes Controllers
		$this->glowing_boxes_controllers( $element );

		// // Color Balls Controllers
		$this->color_balls_controllers( $element );

		if( !$section_start ) {
			$element->add_control(
				'eael_custom_cursor_apply_changes_3',
				[
					'label'     => '',
					'show_label' => false,
					'label_block' => true,
					'type'      => Controls_Manager::BUTTON,
					'button_type' => 'danger',
					'text'      => __( 'Apply Changes', 'essential-addons-elementor' ),
					'event'     => 'eael:custom_cursor:apply_changes',
					'condition' => [
						'eael_cursor_trail_show' => 'yes'
					]
				]
			);
		}

		$element->end_controls_section();
	}

	private function custom_cursor_controllers( $element, $tab = '' ) {
		
		$condition = [
			'eael_enable_custom_cursor' => 'yes'
		];

		if( '_pointer' === $tab ) {
			$condition[ 'eael_enable_pointer_cursor' ] = 'yes';
		}

		$element->add_control(
			'eael_custom_cursor_type' . $tab,
			[
				'label'     => __( 'Type', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'regular' => [
						'title' => __( 'Default', 'essential-addons-elementor' ),
						'icon'  => 'eicon-apps'
					],
					'circle' => [
						'title' => __( 'Circle', 'essential-addons-elementor' ),
						'icon'  => 'eicon-circle-o'
					],
					'icon'   => [
						'title' => __( 'Icon', 'essential-addons-elementor' ),
						'icon'  => 'eicon-favorite'
					],
					'image' => [
						'title' => __( 'Image', 'essential-addons-elementor' ),
						'icon'  => 'eicon-image'
					],
					'svg_code'   => [
						'title' => __( 'SVG Code', 'essential-addons-elementor' ),
						'icon'  => 'eicon-code'
					],
				],
				'default'   => 'icon',
				'condition' => $condition
			]
		);

		$condition[ 'eael_custom_cursor_type' . $tab ] = 'regular';

		$element->add_control(
			'eael_custom_cursor_regular' . $tab,
			[
				'label'     => __( 'Cursor', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => [
					// General
					'auto'          => __( 'Auto', 'essential-addons-elementor' ),
					'default'       => __( 'Default', 'essential-addons-elementor' ),
					'none'          => __( 'None', 'essential-addons-elementor' ),
					'context-menu'  => __( 'Context Menu', 'essential-addons-elementor' ),
					'help'          => __( 'Help', 'essential-addons-elementor' ),

					// Selection
					'pointer'       => __( 'Pointer', 'essential-addons-elementor' ),
					'progress'      => __( 'Progress', 'essential-addons-elementor' ),
					'wait'          => __( 'Wait', 'essential-addons-elementor' ),
					'cell'          => __( 'Cell', 'essential-addons-elementor' ),
					'crosshair'     => __( 'Crosshair', 'essential-addons-elementor' ),
					'text'          => __( 'Text', 'essential-addons-elementor' ),
					'vertical-text' => __( 'Vertical Text', 'essential-addons-elementor' ),

					// Drag & Drop
					'alias'         => __( 'Alias', 'essential-addons-elementor' ),
					'copy'          => __( 'Copy', 'essential-addons-elementor' ),
					'move'          => __( 'Move', 'essential-addons-elementor' ),
					'no-drop'       => __( 'No Drop', 'essential-addons-elementor' ),
					'not-allowed'   => __( 'Not Allowed', 'essential-addons-elementor' ),
					'grab'          => __( 'Grab', 'essential-addons-elementor' ),
					'grabbing'      => __( 'Grabbing', 'essential-addons-elementor' ),

					// Resize
					'e-resize'      => __( 'East Resize', 'essential-addons-elementor' ),
					'n-resize'      => __( 'North Resize', 'essential-addons-elementor' ),
					'ne-resize'     => __( 'Northeast Resize', 'essential-addons-elementor' ),
					'nw-resize'     => __( 'Northwest Resize', 'essential-addons-elementor' ),
					's-resize'      => __( 'South Resize', 'essential-addons-elementor' ),
					'se-resize'     => __( 'Southeast Resize', 'essential-addons-elementor' ),
					'sw-resize'     => __( 'Southwest Resize', 'essential-addons-elementor' ),
					'w-resize'      => __( 'West Resize', 'essential-addons-elementor' ),
					'ew-resize'     => __( 'East-West Resize', 'essential-addons-elementor' ),
					'ns-resize'     => __( 'North-South Resize', 'essential-addons-elementor' ),
					'nesw-resize'   => __( 'NE-SW Resize', 'essential-addons-elementor' ),
					'nwse-resize'   => __( 'NW-SE Resize', 'essential-addons-elementor' ),
					'col-resize'    => __( 'Column Resize', 'essential-addons-elementor' ),
					'row-resize'    => __( 'Row Resize', 'essential-addons-elementor' ),

					// Zoom
					'zoom-in'       => __( 'Zoom In', 'essential-addons-elementor' ),
					'zoom-out'      => __( 'Zoom Out', 'essential-addons-elementor' ),
				],
				'default'   => 'auto',
				'condition' => $condition
			]
		);

		$condition['eael_custom_cursor_type' . $tab] = 'icon';
        $element->add_control(
			'eael_custom_cursor_icon' . $tab,
			[
				'label'     => '',
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-paper-plane',
					'library' => 'fa-solid'
				],
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_icon_size' . $tab,
			[
				'label'     => __( 'Icon Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 128
					]
				],
				'default'   => [
					'size' => 20,
					'unit' => 'px'
				],
				'condition' => $condition,
				'description' => __( 'Size control does not apply for SVG icons.', 'essential-addons-elementor' )
			]
		);

		$element->add_control(
			'eael_custom_cursor_icon_color' . $tab,
			[
				'label'     => __( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'condition' => $condition,
				'global'    => [
					'active' => false
				],
				'description' => __( 'Color control does not apply for SVG icons.', 'essential-addons-elementor' )
			]
		);

		$condition['eael_custom_cursor_type' . $tab] = 'image';
        $element->add_control(
			'eael_custom_cursor_image' . $tab,
			[
				'label'     => '',
				'type'      => Controls_Manager::MEDIA,
                'ai' => [
					'active' => false,
				],
				'condition' => $condition
			]
		);

		$condition['eael_custom_cursor_type' . $tab] = 'svg_code';
		$element->add_control(
			'eael_custom_cursor_svg_code' . $tab,
			[
				'label'     => '',
				'type'      => Controls_Manager::TEXTAREA,
				'ai'        => [ 'active' => false ],
				'placeholder' => __( 'Paste your SVG code here', 'essential-addons-elementor' ),
				'default'   => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" id="Layer_1" width="100" height="100" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve"><g><path fill="#F76D57" d="M32,52.789l-12-18C18.5,32,16,28.031,16,24c0-8.836,7.164-16,16-16s16,7.164,16,16   c0,4.031-2.055,8-4,10.789L32,52.789z"/><g><path fill="#394240" d="M32,0C18.746,0,8,10.746,8,24c0,5.219,1.711,10.008,4.555,13.93c0.051,0.094,0.059,0.199,0.117,0.289    l16,24C29.414,63.332,30.664,64,32,64s2.586-0.668,3.328-1.781l16-24c0.059-0.09,0.066-0.195,0.117-0.289    C54.289,34.008,56,29.219,56,24C56,10.746,45.254,0,32,0z M44,34.789l-12,18l-12-18C18.5,32,16,28.031,16,24    c0-8.836,7.164-16,16-16s16,7.164,16,16C48,28.031,45.945,32,44,34.789z"/><circle fill="#394240" cx="32" cy="24" r="8"/></g></g></svg>',
				'condition' => $condition
			]
		);

		$condition['eael_custom_cursor_type' . $tab] = [ 'image', 'svg_code' ];
        $element->add_control(
			'eael_custom_cursor_image_notice' . $tab,
			[
				'type'        => Controls_Manager::NOTICE,
                'notice_type' => 'warning',
                'content'     => __( 'Cursor image/svg must not be larger than 128x128 pixels. For best compatibility across browsers, a 32x32 pixel size is recommended.', 'essential-addons-elementor' ),
				'condition'   => $condition
			]
		);

		$condition['eael_custom_cursor_type' . $tab] = 'circle';
		$element->add_control(
			'eael_custom_cursor_circle_heading' . $tab,
			[
				'label'     => __( 'Circle', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_type' . $tab,
			[
				'label'     => __( 'Type', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'solid' => [
						'title' => __( 'Solid', 'essential-addons-elementor' ),
						'icon'  => 'eicon-menu-bar'
					],
					'dotted' => [
						'title' => __( 'Dotted', 'essential-addons-elementor' ),
						'icon'  => 'eicon-handle'
					],
					'dashed' => [
						'title' => __( 'Dashed', 'essential-addons-elementor' ),
						'icon'  => 'eicon-library-list'
					],
				],
				'default'   => 'solid',
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_thickness' . $tab,
			[
				'label'     => __( 'Thickness', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 128
					]
				],
				'default'   => [
					'size' => 2,
					'unit' => 'px'
				],
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_color' . $tab,
			[
				'label'     => __( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'condition' => $condition,
				'global'    => [
					'active' => false
				]
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_size' . $tab,
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 128
					]
				],
				'default'   => [
					'size' => 20,
					'unit' => 'px'
				],
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_radius' . $tab,
			[
				'label'     => __( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'%' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 50,
					'unit' => '%'
				],
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_dot_heading' . $tab,
			[
				'label'     => __( 'Inner Dot', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_dot_show' . $tab,
			[
				'label'     => __( 'Show', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'essential-addons-elementor' ),
				'label_off' => __( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'   => 'yes',
				'condition' => $condition
			]
		);

		$condition['eael_custom_cursor_circle_dot_show' . $tab] = 'yes';
		$element->add_control(
			'eael_custom_cursor_circle_dot_color' . $tab,
			[
				'label'     => __( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'condition' => $condition,
				'global'    => [
					'active' => false
				]
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_dot_size' . $tab,
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 128
					]
				],
				'default'   => [
					'size' => 8,
					'unit' => 'px'
				],
				'condition' => $condition
			]
		);

		$element->add_control(
			'eael_custom_cursor_circle_dot_radius' . $tab,
			[
				'label'     => __( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'%' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 50,
					'unit' => '%'
				],
				'condition' => $condition
			]
		);
	}

	/**
	 * Following Dots Trail Controllers
	 *
	 * @param object $element
	 * @return void
	 */
	private function following_dots_trail_controllers( $element ) {

		$element->add_control(
			'eael_cursor_trail_count',
			[
				'label'       => esc_html__( 'Dot Count', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 20,
				'step'        => 1,
				'default'     => 12,
				'label_block' => false,
				'condition'   => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_color_type',
			[
				'label' => esc_html__( 'Color Type', 'essential-addons-elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'single' => [
						'title' => esc_html__( 'Single', 'essential-addons-elementor' ),
						'icon' => 'eicon-circle',
					],
					'multiple' => [
						'title' => esc_html__( 'Multiple', 'essential-addons-elementor' ),
						'icon' => 'eicon-spinner',
					],
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				],
				'default' => 'single',
				'toggle' => false,
			]
		);

		$element->add_control(
			'eael_cursor_trail_color',
			[
				'label'     => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'condition' => [
					'eael_cursor_trail_show'       => 'yes',
					'eael_cursor_trail_effect'     => 'following_dots',
					'eael_cursor_trail_color_type' => 'single'
				],
				'global'    => [
					'active' => false
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_colors_popover_toggle',
			[
				'label'        => esc_html__( 'Colors', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'essential-addons-elementor' ),
				'label_on'     => esc_html__( 'Custom', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'eael_cursor_trail_show'       => 'yes',
					'eael_cursor_trail_effect'     => 'following_dots',
					'eael_cursor_trail_color_type' => 'multiple'
				]
			]
		);
		
		$element->start_popover();

		for( $i = 1; $i <= 20; $i++ ) {
			$con_val = range( $i, 20);
			$element->add_control(
				'eael_cursor_trail_color_' . $i,
				[
					'label'     => __( 'Color', 'essential-addons-elementor' ) . ' ' . $i,
					'type'      => Controls_Manager::COLOR,
					'default'   => '#1E1F24',
					'global'    => [
						'active' => false
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'eael_cursor_trail_show',
								'operator' => '===',
								'value' => 'yes'
							],
							[
								'name' => 'eael_cursor_trail_effect',
								'operator' => '===',
								'value' => 'following_dots'
							],
							[
								'name' => 'eael_cursor_trail_count',
								'operator' => 'in',
								'value' => $con_val
							],
							[
								'name' => 'eael_cursor_trail_color_type',
								'operator' => '===',
								'value' => 'multiple'
							],
						]
					]
				]
			);
		}
		
		$element->end_popover();

		$element->add_control(
			'eael_cursor_trail_size',
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 128
					]
				],
				'default'   => [
					'size' => 10,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_speed',
			[
				'label'     => __( 'Speed', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 50
					]
				],
				'default'   => [
					'size' => 8,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_radius',
			[
				'label'     => __( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'%' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 50,
					'unit' => '%'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_opacity',
			[
				'label'     => __( 'Opacity', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'step'      => 1,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 10
					]
				],
				'default'   => [
					'size' => 5,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'following_dots'
				]
			]
		);
	}

	/**
	 * Ghost Following Controllers
	 *
	 * @param object $element
	 * @return void
	 */
	private function phantomsmoke_controllers( $element ) {

		$element->add_control(
			'eael_cursor_trail_ghost_size',
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 10,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'phantomsmoke'
				]
			]
		);

		$element->add_control(
			'eael_cursor_trail_ghost_color',
			[
				'label'     => __( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'global'    => [
					'active' => false
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'phantomsmoke'
				]
			]
		);
	}

	/**
	 * Ghost Following Cursor Controllers
	 *
	 * @param object $element
	 * @return void
	 */
	private function ghost_following_controllers( $element ) {

		$element->add_control(
			'eael_cursor_ghost_following_size',
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100,
						'step' => 1
					]
				],
				'default'   => [
					'size' => 10,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'spiritecho'
				]
			]
		);

		$element->add_control(
			'eael_cursor_ghost_following_color',
			[
				'label'     => __( 'Ghost Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'global'    => [
					'active' => false
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'spiritecho'
				]
			]
		);

		$element->add_control(
			'eael_cursor_ghost_following_eye_color',
			[
				'label'     => __( 'Eye Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'global'    => [
					'active' => false
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'spiritecho'
				]
			]
		);
	}

	private function snowflake_cursor_controllers( $element ) {

		$element->add_control(
			'eael_cursor_snowflake_emojis',
			[
				'label'     => __( 'Snowflake Count', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::TEXTAREA,
				'ai'        => [ 'active' => false ],
				'default'   => '❄️',
				'label_block' => true,
				'description' => __( 'Enter emoji for snowflakes separated by comma.', 'essential-addons-elementor' ),
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'frostsparkles'
				]
			]
		);

		$element->add_control(
			'eael_cursor_snowflake_windy_effect',
			[
				'label'     => __( 'Windy Effect', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'frostsparkles'
				]
			]
		);
	}

	private function ink_line_controllers( $element ) {
		$element->add_control(
			'eael_cursor_ink_line_color',
			[
				'label'     => __( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E1F24',
				'global'    => [
					'active' => false
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'inktrail'
				]
			]
		);
	}

	private function glowing_boxes_controllers( $element ) {
		$element->add_control(
			'eael_cursor_glowing_boxes_opacity',
			[
				'label'     => __( 'Opacity', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 10
					]
				],
				'default'   => [
					'size' => 10,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);

		$element->add_control(
			'eael_cursor_glowing_boxes_size',
			[
				'label'     => __( 'Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 50,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);

		$element->add_control(
			'eael_cursor_glowing_boxes_border_radius',
			[
				'label'     => __( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 1,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);

		$element->add_control(
			'eael_cursor_glowing_boxes_trail_length',
			[
				'label'     => __( 'Trail Length', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 20,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);

		$element->add_control(
			'eael_cursor_glowing_boxes_interval',
			[
				'label'     => __( 'Interval', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 10,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);

		$element->add_control(
			'eael_cursor_glowing_boxes_hue_speed',
			[
				'label'     => __( 'Hue Speed', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 100
					]
				],
				'default'   => [
					'size' => 6,
					'unit' => 'px'
				],
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'glowingBoxes'
				]
			]
		);
	}

	private function color_balls_controllers( $element ) {
		$element->add_control(
			'eael_cursor_color_balls_popover_toggle',
			[
				'label'        => esc_html__( 'Colors', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'label_off'    => esc_html__( 'Default', 'essential-addons-elementor' ),
				'label_on'     => esc_html__( 'Custom', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description' => __( 'You can set custom colors for the color balls. Leave the fields empty to use the default colors. You can use up to 7 colors.', 'essential-addons-elementor' ),
				'condition' => [
					'eael_cursor_trail_show' => 'yes',
					'eael_cursor_trail_effect' => 'colorBalls'
				]
			]
		);
		
		$element->start_popover();
		$colors = [ '#edeeef', '#c3c5ca', '#9b9fa7', '#767a81', '#54575c', '#343639', '#17181a' ];
		for( $i = 1; $i <= 7; $i++ ) {
			$element->add_control(
				'eael_cursor_color_balls_color_' . $i,
				[
					'label'  => esc_html__( 'Color ' . $i, 'essential-addons-elementor' ),
					'type'   => Controls_Manager::COLOR,
					'global' => [
						'active' => false
					],
					'default' => !empty($colors[$i - 1]) ? $colors[$i - 1] : '',
					'condition' => [
						'eael_cursor_trail_show' => 'yes',
						'eael_cursor_trail_effect' => 'colorBalls'
					]
				]
			);
		}
		
		$element->end_popover();
	}

	/**
	 * Adjust Item and Colors
	 *
	 * @param int $item_count
	 * @param array $assigned_colors
	 * @return array
	 */
	private function adjust_item_and_colors( $item_count, $assigned_colors ) {
		// Get colors from settings
		$colors = array_filter(
				array_map(
					function($color) {
						return trim(str_replace(['\'', '"'], '', $color));
					}, 
					$assigned_colors
				),
				function($color) {
					return $color !== '';
				}
			);
		
		// Assign colors based on count relationship
		$assigned_colors = [];
		$color_count = count($colors);
		
		if ($color_count > 0) {
			// Assign colors to each trail segment using modulo for overflow
			for ($i = 0; $i < $item_count; $i++) {
				$assigned_colors[] = $colors[$i % $color_count];
			}
		}

		return $assigned_colors;
	}

	private function handle_trail_icon( $settings ) {
		$icon = '';
		if( 'icon' === $settings['icon_type'] ) {
			$svg = '';
			$attributes = [
				'height' => $settings['icon_size']['size'] ?? 20,
				'width'  => $settings['icon_size']['size'] ?? 20,
				'fill'   => $settings['icon_color'] ?? '#1E1F24',
			];
			if( 'svg' === $settings['icon']['library'] ) {
				$svg = Icons_Manager::try_get_icon_html( $settings['icon'], [ 'aria-hidden' => 'true' ] );
			} else {
				$svg = Helper::get_svg_by_icon( $settings['icon'], $attributes );
			}

			if( ! empty( $svg ) ) {
				$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg );
			}
		} else if( 'image' === $settings['icon_type'] ) {
			$icon = $settings['trail_image']['url'] ?? '';
		} else if( 'code' === $settings['icon_type'] && !empty( trim( $settings['svg_code'] ) ) ) {
			$icon = 'data:image/svg+xml;base64,' . base64_encode( $settings['svg_code'] );
		}

		return $icon;
	}

	private function following_dots_trail_settings( $settings, &$cursor_settings ) {
		$cursor_settings['trail_size']    = $settings['eael_cursor_trail_size']['size'] ?? 10;
		$cursor_settings['trail_opacity'] = $settings['eael_cursor_trail_opacity']['size'] ?? 5;
		$cursor_settings['trail_opacity'] = $cursor_settings['trail_opacity'] / 10;
		$cursor_settings['trail_radius']  = $settings['eael_cursor_trail_radius']['size'] ?? 50;
		$cursor_settings['trail_speed']   = ( $settings['eael_cursor_trail_speed']['size'] ?? 8 ) / 100;
		$cursor_settings['trail_count']   = $settings['eael_cursor_trail_count'] ?? 12;
		$dot_colors = [];
		if( 'single' === $settings['eael_cursor_trail_color_type'] ) {
			$dot_colors = array_fill( 0, $cursor_settings['trail_count'], $settings['eael_cursor_trail_color'] );
		} else {
			for( $i = 1; $i <= $cursor_settings['trail_count']; $i++ ) {
				if( ! empty( $settings['eael_cursor_trail_color_' . $i] ) ) {
					$dot_colors[] = $settings['eael_cursor_trail_color_' . $i];
				}
			}
		}
		$cursor_settings['trail_colors'] = $this->adjust_item_and_colors( $cursor_settings['trail_count'], $dot_colors );
	}

	private function color_swipe_trail_settings( $settings, &$cursor_settings ) {
		$cursor_settings['trail_length'] = $settings['eael_cursor_color_swipe_trail_length']['size'] ?? 30;
		$cursor_settings['trail_size']   = $settings['eael_cursor_color_swipe_trail_size']['size'] ?? 10;
		$cursor_settings['trail_count']  = $settings['eael_cursor_color_swipe_trail_count'] ?? 4;

		$dot_colors = [];
		for( $i = 1; $i <= $cursor_settings['trail_count']; $i++ ) {
			if( ! empty( $settings['eael_cursor_color_swipe_trail_color_' . $i] ) ) {
				$dot_colors[] = $settings['eael_cursor_color_swipe_trail_color_' . $i];
			}
		}

		$cursor_settings['trail_colors'] = $this->adjust_item_and_colors( $cursor_settings['trail_count'], $dot_colors );
	}

	private function phantomsmoke_settings( $settings, &$cursor_settings ) {
		$cursor_settings['trail_ghost_size'] = ( $settings['eael_cursor_trail_ghost_size']['size'] ?? 3 ) / 100;
		$cursor_settings['trail_ghost_color'] = $settings['eael_cursor_trail_ghost_color'] ?? '';
	}

	private function ghost_following_settings( $settings, &$cursor_settings ) {
		$cursor_settings['ghost_following_size'] = ( $settings['eael_cursor_ghost_following_size']['size'] ?? 7 ) / 10;
		$cursor_settings['ghost_following_color'] = $settings['eael_cursor_ghost_following_color'] ?? '#1E1F24';
		$cursor_settings['ghost_following_eye_color'] = $settings['eael_cursor_ghost_following_eye_color'] ?? '#ffffff';
	}

	private function trailing_cursor_settings( $settings, &$cursor_settings ) {
		$_settings = [
			'icon' => $settings['eael_cursor_trail_icon'] ?? '',
			'color' => $settings['eael_cursor_trail_icon_color'] ?? '',
			'size' => $settings['eael_cursor_trail_icon_size']['size'] ?? '',
			'icon_type' => $settings['eael_cursor_trail_icon_type'] ?? '',
			'trail_image' => $settings['eael_cursor_trail_image'] ?? '',
			'svg_code' => $settings['eael_cursor_trail_svg_code'] ?? '',
		];
		$trail_icon = $this->handle_trail_icon( $_settings );
		if( ! empty( $trail_icon ) ) {
			$cursor_settings['trail_icon'] = $trail_icon;
		}
		
		// Add particles settings
		$cursor_settings['trail_particles'] = $settings['eael_cursor_trailing_particles']['size'] ?? 15;
		
		// Add rate settings
		$cursor_settings['trail_rate'] = $settings['eael_cursor_trailing_rate']['size'] ? $settings['eael_cursor_trailing_rate']['size'] / 100 : 0.4;
	}

	private function prepare_pointer_selector( $wrapper, $settings ) {
		$selector = '';
		$pointer_elements = $settings['eael_pointer_selectors'] ?? [];

		if( empty( $pointer_elements ) ) {
			return $selector;
		}

		$selector = implode( ', ', array_map( function( $element ) use ( $wrapper ) {
			return $wrapper . ' ' . $element;
		}, $pointer_elements ) );
		
		return $selector;
	}

	private function get_circle_cursor_settings( $settings, &$cursor_settings, $state = '' ){
		$cursor_settings['cursor_type' . $state] 	 = $settings['eael_custom_cursor_type' . $state];
		$cursor_settings['circle_type' . $state]      = $settings['eael_custom_cursor_circle_type' . $state] ?? 'solid';
		$cursor_settings['circle_thickness' . $state] = $settings['eael_custom_cursor_circle_thickness' . $state]['size'] ?? 2;
		$cursor_settings['circle_color' . $state]     = $settings['eael_custom_cursor_circle_color' . $state] ?? '#1E1F24';
		$cursor_settings['circle_size' . $state]      = $settings['eael_custom_cursor_circle_size' . $state]['size'] ?? 20;
		$cursor_settings['circle_radius' . $state]    = $settings['eael_custom_cursor_circle_radius' . $state]['size'] ?? 50;
		$cursor_settings['dot_show' . $state]         = isset($settings['eael_custom_cursor_circle_dot_show' . $state]) && 'yes' === $settings['eael_custom_cursor_circle_dot_show' . $state];


		if( 'yes' === $settings['eael_custom_cursor_circle_dot_show' . $state] ) {
			$cursor_settings['dot_color' . $state]  = $settings['eael_custom_cursor_circle_dot_color' . $state] ?? '#fff';
			$cursor_settings['dot_size' . $state]   = $settings['eael_custom_cursor_circle_dot_size' . $state]['size'] ?? 8;
			$cursor_settings['dot_radius' . $state] = $settings['eael_custom_cursor_circle_dot_radius' . $state]['size'] ?? 8;
		}
	}

	public function before_render( $element, $page_render = false, $global_settings = [] ) {

		$settings = $element->get_settings_for_display();
		$element_id = $element->get_id();

		if( $page_render && ! empty( $global_settings ) ) {
			$settings = $global_settings;
		}
		$cursor_settings = [];
		if ( isset( $settings['eael_enable_custom_cursor'] ) && "yes" === $settings['eael_enable_custom_cursor'] ) {
			$element->add_render_attribute( '_wrapper', 'data-eael-cursor', $settings['eael_custom_cursor_type'] );
			$cursor = $this->get_cursor( $settings );
			if( ! empty( $cursor ) ) {
				$element->add_render_attribute( '_wrapper', 'style', 'cursor: ' . $cursor . ';' );
				if( $page_render ) {
					echo '<style id="eael-cursor-style-' . esc_attr( $element_id ) . '">body{ cursor: ' . $cursor . '; }</style>';
				}
			} else if( empty( $cursor ) && 'circle' === $settings['eael_custom_cursor_type'] ) {
				$this->get_circle_cursor_settings( $settings, $cursor_settings );
			}

			if( 'yes' === $settings['eael_enable_pointer_cursor'] ) {
				$pointer_cursor = $this->get_cursor( $settings, '_pointer' );
				if( ! empty( $pointer_cursor ) ) {
					$wrapper = $page_render ? 'body' : '.elementor-element-' . esc_attr( $element_id );
					echo '<style id="eael-pointer-style-' . esc_attr( $element_id ) . '">' 
					. $this->prepare_pointer_selector( $wrapper, $settings ) . ' { cursor: ' . $pointer_cursor . '; }</style>';
				} else if( empty( $pointer_cursor ) && 'circle' === $settings['eael_custom_cursor_type' . '_pointer'] ) {
					$this->get_circle_cursor_settings( $settings, $cursor_settings, '_pointer' );
				}
			}
		}

		if( isset( $settings['eael_cursor_trail_show'] ) && 'yes' === $settings['eael_cursor_trail_show'] ) {
			$cursor_settings['trail']         = 'yes';
			$cursor_settings['trail_effect']    = $settings['eael_cursor_trail_effect'] ?? 'following_dots';

			if( 'following_dots' === $settings['eael_cursor_trail_effect'] ) {
				$this->following_dots_trail_settings( $settings, $cursor_settings );
			} else if( 'phantomsmoke' === $settings['eael_cursor_trail_effect'] ) {
				$this->phantomsmoke_settings( $settings, $cursor_settings );
			} else if( 'spiritecho' === $settings['eael_cursor_trail_effect'] ) {
				$this->ghost_following_settings( $settings, $cursor_settings );
			} else if( 'frostsparkles' === $settings['eael_cursor_trail_effect'] ) {
				$cursor_settings['trail_windy_efect'] = 'yes' === $settings['eael_cursor_snowflake_windy_effect'];
				$cursor_settings['trail_emojis'] = $settings['eael_cursor_snowflake_emojis'] ?? '';
			} else if( 'inktrail' === $settings['eael_cursor_trail_effect'] ) {
				$cursor_settings['ink_line_color'] = $settings['eael_cursor_ink_line_color'] ?? '#1E1F24';
			} else if( 'glowingBoxes' === $settings['eael_cursor_trail_effect'] ) {
				$cursor_settings['glowing_boxes_opacity'] = $settings['eael_cursor_glowing_boxes_opacity']['size'] ?? 1;
				$cursor_settings['glowing_boxes_size'] = $settings['eael_cursor_glowing_boxes_size']['size'] ?? 50;
				$cursor_settings['glowing_boxes_border_radius'] = $settings['eael_cursor_glowing_boxes_border_radius']['size'] ?? 1;
				$cursor_settings['glowing_boxes_trail_length'] = $settings['eael_cursor_glowing_boxes_trail_length']['size'] ?? 20;
				$cursor_settings['glowing_boxes_interval'] = $settings['eael_cursor_glowing_boxes_interval']['size'] ?? 10;
				$cursor_settings['glowing_boxes_hue_speed'] = $settings['eael_cursor_glowing_boxes_hue_speed']['size'] ?? 2;
			} else if( 'colorBalls' === $settings['eael_cursor_trail_effect'] ) {
				for( $i = 1; $i <= 7; $i++ ) {
					$cursor_settings['color_balls_colors'][] = !empty( trim( $settings['eael_cursor_color_balls_color_' . $i] ) ) ? $settings['eael_cursor_color_balls_color_' . $i] : '';
				}
				$cursor_settings['color_balls_colors'] = array_filter( $cursor_settings['color_balls_colors'], function($color) {
					return trim($color) !== '';
				} );
			}

			$cursor_settings['trail_hide_on_idle'] = isset($settings['eael_cursor_trail_hide_on_idle']) && 'yes' === $settings['eael_cursor_trail_hide_on_idle'];
			$cursor_settings['trail_idle_timeout'] = $settings['eael_cursor_trail_idle_timeout']['size'] ?? 3;
		}

		if( ! empty( $cursor_settings ) ) {
			$element->add_render_attribute( '_wrapper', 'data-eael-cursor-settings', wp_json_encode( $cursor_settings ) );
			if( $page_render ) {
				echo '<script>document.body.setAttribute("data-page-id",' . $element->get_id() .');</script>';
				echo '<script type="application/json" id="eael-cursor-trail-settings-' . $element->get_id() . '">'. wp_json_encode( $cursor_settings ) .'</script>';
			}
		}
	}

	private function get_cursor( $settings, $state = '' ) {
		$cursor = '';
		if( 'image' === $settings['eael_custom_cursor_type' . $state] && ! empty( $settings['eael_custom_cursor_image' . $state]['url'] ) ) {
			$cursor = 'url("' . $settings['eael_custom_cursor_image' . $state]['url'] . '") 0 0, auto';
		} else if( 'icon' === $settings['eael_custom_cursor_type' . $state] && ! empty( $settings['eael_custom_cursor_icon' . $state]['value'] ) ) {
			$size = !empty( $settings['eael_custom_cursor_icon_size' . $state]['size'] ) ? $settings['eael_custom_cursor_icon_size' . $state]['size'] : 20;
			$attributes = [
				'height' => $size,
				'width'  => $size,
			];
			$attributes['fill'] = !empty( $settings['eael_custom_cursor_icon_color' . $state] ) ? $settings['eael_custom_cursor_icon_color' . $state] : '#000';
			$svg = '';
			if( 'svg' === $settings['eael_custom_cursor_icon' . $state]['library'] ) {
				$svg = Icons_Manager::try_get_icon_html( $settings['eael_custom_cursor_icon' . $state], [ 'aria-hidden' => 'true' ] );
			} else {
				$svg = Helper::get_svg_by_icon( $settings['eael_custom_cursor_icon' . $state], $attributes );
			}

			if( ! empty( $svg ) ) {
				$svg = base64_encode( $svg );
				$cursor = 'url("data:image/svg+xml;base64,' . $svg . '") 0 0, auto';
			}
		} else if( 'svg_code' === $settings['eael_custom_cursor_type' . $state] && ! empty( trim( $settings['eael_custom_cursor_svg_code' . $state] ) ) ) {
			$svg = base64_encode( $settings['eael_custom_cursor_svg_code' . $state] );
			$cursor = 'url("data:image/svg+xml;base64,' . $svg . '") 0 0, auto';
		} else if( 'regular' === $settings['eael_custom_cursor_type' . $state] && ! empty( $settings['eael_custom_cursor_regular' . $state] ) ) {
			$cursor = $settings['eael_custom_cursor_regular' . $state];
		} 

		return $cursor;
	}

	public function render_custom_cursor_html( $document, $global_settings ) {
		
		if( ! empty( $global_settings['eael_custom_cursor'] ) && $global_settings['eael_custom_cursor']['enabled_globally'] && get_the_ID() !== $global_settings['eael_custom_cursor']['post_id'] ) {
			$settings = $global_settings['eael_custom_cursor'];
			$this->before_render( $document, true, $settings );
		} else if( is_object( $document ) && method_exists( $document, 'get_settings_for_display' ) ){
			$settings = $document->get_settings_for_display();
			$this->before_render( $document, true );
		} else {
			return;
		}
		
		if( isset($settings['eael_custom_cursor_type']) && 'circle' === $settings['eael_custom_cursor_type'] ) {
			wp_enqueue_script( 'eael-custom-cursor-gsap' );
		}

		if ( isset( $settings['eael_cursor_trail_show'] ) && 'yes' === $settings['eael_cursor_trail_show'] ) {
			if( 'following_dots' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-custom-cursor-gsap' );
			} else if( 'phantomsmoke' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-smoky-ghost-trail' );
			} else if( 'spiritecho' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-ghost-following' );
				wp_enqueue_style( 'eael-ghost-following' );
			} else if( 'trailparticles' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-pointer-particles' );
			} else if( 'inktrail' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-ink-line' );
			} else if( 'glowingBoxes' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-glowing-boxes' );
			} else if( 'colorBalls' === $settings['eael_cursor_trail_effect'] ) {
				wp_enqueue_script( 'eael-color-balls' );
			} else {
				wp_enqueue_script( 'eael-90s-cursor-effects' );
			}
		}

		if( ( isset($settings['eael_enable_custom_cursor']) && 'yes' === $settings['eael_enable_custom_cursor'] ) || ( isset($settings['eael_cursor_trail_show']) && 'yes' === $settings['eael_cursor_trail_show'] ) ) {
			wp_enqueue_script( 'eael-custom-cursor' );
		}
	}

	public function register_assets() {
		wp_register_script(
			'eael-custom-cursor-gsap',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/gsap/gsap.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-90s-cursor-effects',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/90s-cursor-effects.min.js',
			['jquery'],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-following-cat',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/following-cat.min.js',
			['jquery'],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-snake-bugs',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/snake-bugs.min.js',
			['jquery'],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-smoky-ghost-trail',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/smoky-ghost-trail.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-ghost-following',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/ghost-following.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_style(
			'eael-ghost-following',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/css/lib-view/cursor/ghost-following.min.css',
			[],
			EAEL_PLUGIN_VERSION
		);

		wp_register_script(
			'eael-pointer-particles',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/pointer-perticle.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-ink-line',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/ink-line.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-glowing-boxes',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/glowing-boxes.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-color-balls',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/lib-view/cursor/color-balls.min.js',
			[],
			EAEL_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'eael-custom-cursor',
			EAEL_PRO_PLUGIN_URL . 'assets/front-end/js/view/custom-cursor.min.js',
			['jquery'],
			EAEL_PLUGIN_VERSION,
			true
		);
	}

	public function global_settings( $global_settings, $document, $post_id ) {
		$is_global = $document->get_settings('eael_enable_custom_cursor_global') == 'yes';
		if( ! $is_global ) {
			$global_settings['eael_custom_cursor'] = [
				'enabled' => false,
				'post_id' => $post_id,
				'enabled_globally' => false
			];
			return $global_settings;
		}

		$settings = [
			'enabled' => $document->get_settings('eael_enable_custom_cursor') == 'yes',
			'post_id' => $post_id,
			'enabled_globally' => $is_global,
			'global_display_condition' => $document->get_settings('eael_ext_custom_cursor_global_display_condition'),
			
			// Basic cursor settings
			'eael_enable_custom_cursor' => $document->get_settings('eael_enable_custom_cursor'),
			'eael_custom_cursor_type' => $document->get_settings('eael_custom_cursor_type'),
			'eael_cursor_trail_hide_on_idle' => $document->get_settings('eael_cursor_trail_hide_on_idle'),
		];

		if( 'yes' === $settings['eael_enable_custom_cursor'] ) {
			if( 'regular' === $settings['eael_custom_cursor_type'] ) {
				$settings['eael_custom_cursor_regular'] = $document->get_settings('eael_custom_cursor_regular');
			} else if( 'image' === $settings['eael_custom_cursor_type'] ) {
				$settings['eael_custom_cursor_image'] = $document->get_settings('eael_custom_cursor_image');
			} else if( 'icon' === $settings['eael_custom_cursor_type'] ) {
				$settings['eael_custom_cursor_icon'] = $document->get_settings('eael_custom_cursor_icon');
				$settings['eael_custom_cursor_icon_color'] = $document->get_settings('eael_custom_cursor_icon_color');
				$settings['eael_custom_cursor_icon_size'] = $document->get_settings('eael_custom_cursor_icon_size');
				$settings['eael_icon_to_svg_path'] = $document->get_settings('eael_icon_to_svg_path');
			} else if( 'svg_code' === $settings['eael_custom_cursor_type'] ) {
				$settings['eael_custom_cursor_svg_code'] = $document->get_settings('eael_custom_cursor_svg_code');
			} else if( 'circle' === $settings['eael_custom_cursor_type'] ) {
				$settings['eael_custom_cursor_circle_type']       = $document->get_settings('eael_custom_cursor_circle_type');
				$settings['eael_custom_cursor_circle_thickness']  = $document->get_settings('eael_custom_cursor_circle_thickness');
				$settings['eael_custom_cursor_circle_color']      = $document->get_settings('eael_custom_cursor_circle_color');
				$settings['eael_custom_cursor_circle_size']       = $document->get_settings('eael_custom_cursor_circle_size');
				$settings['eael_custom_cursor_circle_radius']     = $document->get_settings('eael_custom_cursor_circle_radius');
				$settings['eael_custom_cursor_circle_dot_show']   = $document->get_settings('eael_custom_cursor_circle_dot_show');
				$settings['eael_custom_cursor_circle_dot_color']  = $document->get_settings('eael_custom_cursor_circle_dot_color');
				$settings['eael_custom_cursor_circle_dot_size']   = $document->get_settings('eael_custom_cursor_circle_dot_size');
				$settings['eael_custom_cursor_circle_dot_radius'] = $document->get_settings('eael_custom_cursor_circle_dot_radius');
			}
		}

		$settings['eael_enable_pointer_cursor'] = $document->get_settings('eael_enable_pointer_cursor');
		if( 'yes' === $settings['eael_enable_pointer_cursor'] ) {
			$settings['eael_custom_cursor_type_pointer'] = $document->get_settings('eael_custom_cursor_type_pointer');
			if( 'image' === $settings['eael_custom_cursor_type_pointer'] ) {
				$settings['eael_custom_cursor_image_pointer'] = $document->get_settings('eael_custom_cursor_image_pointer');
			} else if( 'icon' === $settings['eael_custom_cursor_type_pointer'] ) {
				$settings['eael_custom_cursor_icon_pointer'] = $document->get_settings('eael_custom_cursor_icon_pointer');
				$settings['eael_custom_cursor_icon_color_pointer'] = $document->get_settings('eael_custom_cursor_icon_color_pointer');
				$settings['eael_custom_cursor_icon_size_pointer'] = $document->get_settings('eael_custom_cursor_icon_size_pointer');
				$settings['eael_icon_to_svg_path_pointer'] = $document->get_settings('eael_icon_to_svg_path_pointer');
			} else if( 'svg_code' === $settings['eael_custom_cursor_type_pointer'] ) {
				$settings['eael_custom_cursor_svg_code_pointer'] = $document->get_settings('eael_custom_cursor_svg_code_pointer');
			} else if( 'circle' === $settings['eael_custom_cursor_type_pointer'] ) {
				$settings['eael_custom_cursor_circle_type_pointer']       = $document->get_settings('eael_custom_cursor_circle_type_pointer');
				$settings['eael_custom_cursor_circle_thickness_pointer']  = $document->get_settings('eael_custom_cursor_circle_thickness_pointer');
				$settings['eael_custom_cursor_circle_color_pointer']      = $document->get_settings('eael_custom_cursor_circle_color_pointer');
				$settings['eael_custom_cursor_circle_size_pointer']       = $document->get_settings('eael_custom_cursor_circle_size_pointer');
				$settings['eael_custom_cursor_circle_radius_pointer']     = $document->get_settings('eael_custom_cursor_circle_radius_pointer');
				$settings['eael_custom_cursor_circle_dot_show_pointer']   = $document->get_settings('eael_custom_cursor_circle_dot_show_pointer');
				$settings['eael_custom_cursor_circle_dot_color_pointer']  = $document->get_settings('eael_custom_cursor_circle_dot_color_pointer');
				$settings['eael_custom_cursor_circle_dot_size_pointer']   = $document->get_settings('eael_custom_cursor_circle_dot_size_pointer');
				$settings['eael_custom_cursor_circle_dot_radius_pointer'] = $document->get_settings('eael_custom_cursor_circle_dot_radius_pointer');
			}
		}

		if( 'yes' === $document->get_settings('eael_cursor_trail_show') ) {
			$settings['eael_cursor_trail_show'] = $document->get_settings('eael_cursor_trail_show');
			$settings['eael_cursor_trail_effect'] = $document->get_settings('eael_cursor_trail_effect');

			if( 'following_dots' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_trail_size']    = $document->get_settings('eael_cursor_trail_size');
				$settings['eael_cursor_trail_opacity'] = $document->get_settings('eael_cursor_trail_opacity');
				$settings['eael_cursor_trail_radius']  = $document->get_settings('eael_cursor_trail_radius');
				$settings['eael_cursor_trail_speed']   = $document->get_settings('eael_cursor_trail_speed');
				$settings['eael_cursor_trail_count']   = $document->get_settings('eael_cursor_trail_count');
				$settings['eael_cursor_trail_color_type'] = $document->get_settings('eael_cursor_trail_color_type');
				$settings['eael_cursor_trail_color'] = $document->get_settings('eael_cursor_trail_color');
				if( ! empty( $settings['eael_cursor_trail_count'] ) ) {
					for( $i = 1; $i <= $settings['eael_cursor_trail_count']; $i++ ) {
						$settings['eael_cursor_trail_color_' . $i] = $document->get_settings('eael_cursor_trail_color_' . $i);
					}
				}
			} else if( 'phantomsmoke' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_trail_ghost_size'] = $document->get_settings('eael_cursor_trail_ghost_size');
				$settings['eael_cursor_trail_ghost_color'] = $document->get_settings('eael_cursor_trail_ghost_color');
			} else if( 'spiritecho' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_ghost_following_size'] = $document->get_settings('eael_cursor_ghost_following_size');
				$settings['eael_cursor_ghost_following_color'] = $document->get_settings('eael_cursor_ghost_following_color');
				$settings['eael_cursor_ghost_following_eye_color'] = $document->get_settings('eael_cursor_ghost_following_eye_color');
			} else if( 'frostsparkles' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_snowflake_emojis'] = $document->get_settings('eael_cursor_snowflake_emojis');
				$settings['eael_cursor_snowflake_windy_effect'] = $document->get_settings('eael_cursor_snowflake_windy_effect');
			} else if( 'inktrail' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_ink_line_color'] = $document->get_settings('eael_cursor_ink_line_color');
			} else if( 'glowingBoxes' === $settings['eael_cursor_trail_effect'] ) {
				$settings['eael_cursor_glowing_boxes_opacity'] = $document->get_settings('eael_cursor_glowing_boxes_opacity');
				$settings['eael_cursor_glowing_boxes_size'] = $document->get_settings('eael_cursor_glowing_boxes_size');
				$settings['eael_cursor_glowing_boxes_border_radius'] = $document->get_settings('eael_cursor_glowing_boxes_border_radius');
				$settings['eael_cursor_glowing_boxes_trail_length'] = $document->get_settings('eael_cursor_glowing_boxes_trail_length');
				$settings['eael_cursor_glowing_boxes_interval'] = $document->get_settings('eael_cursor_glowing_boxes_interval');
				$settings['eael_cursor_glowing_boxes_hue_speed'] = $document->get_settings('eael_cursor_glowing_boxes_hue_speed');
			} else if( 'colorBalls' === $settings['eael_cursor_trail_effect'] ) {
				for( $i = 1; $i <= 7; $i++ ) {
					$settings['eael_cursor_color_balls_color_' . $i] = $document->get_settings('eael_cursor_color_balls_color_' . $i);
				}
			}
		}

		if( 'yes' === $settings['eael_cursor_trail_hide_on_idle'] ) {
			$settings['eael_cursor_trail_idle_timeout'] = $document->get_settings('eael_cursor_trail_idle_timeout');
		}

		$global_settings['eael_custom_cursor'] = $settings;
		return $global_settings;
	}
}
