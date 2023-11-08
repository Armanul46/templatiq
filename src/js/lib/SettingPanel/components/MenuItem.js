import { useSettingsState } from '../context/SettingsContext';

export default function MenuItem( { menu } ) {
	const { settingsState } = useSettingsState();
	const { NavLink } = settingsState;

	const activeMenuClass = ( { isActive } ) => {
		const baseClass = 'templatiq-settings-panel-nav-menu-link';

		if ( isActive ) {
			return `${ baseClass } --is-active`;
		}

		return baseClass;
	};

	const menuIconClass = ( menu ) => {
		const baseClass = 'templatiq-settings-panel-nav-menu-icon templatiq-icon';
		return menu.iconClass
			? `${ baseClass } ${ menu.iconClass }`
			: baseClass;
	};

	return (
		<div className="templatiq-settings-panel-nav-menu-item">
			<NavLink to={ `${ menu.path }` } className={ activeMenuClass }>
				<span className={ menuIconClass( menu ) }>{ menu.icon }</span>
				<span className="templatiq-settings-panel-nav-menu-label">
					{ menu.label }
				</span>
			</NavLink>
		</div>
	);
}