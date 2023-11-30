import { useState, useEffect } from '@wordpress/element';
import { useLocation } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import ReactSVG from 'react-inlinesvg';
import { SidebarStyle, SidebarItemStyle } from './style';

import { Accordion, AccordionItem } from '@szhsin/react-accordion';
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import 'react-tabs/style/react-tabs.css';
import ContentLoading from '@components/ContentLoading';

import filterIcon from '@icon/filter.svg';

const Sidebar = ({ updateSelectedFilters }) => {
	const location = useLocation();
	const pathType = location.pathname.split('/').pop();
	
	const [ templates, setTemplates] = useState([]);
	const [ categories, setCategories] = useState([]);
	const [ plugins, setPlugins] = useState([]);
	const [ pluginGroups, setPluginGroups] = useState([]);

	const [ countCategories, setCountCategories] = useState();
	const [ countPlugins, setCountPlugins] = useState();

	const [selectedFilters, setSelectedFilters] = useState([]);
	const [filteredTemplates, setFilteredTemplates] = useState([]);
	const [ templateType, setTemplateType] = useState(pathType ? pathType : 'pack');

	const handleFilter = (key, type) => {
		// Copy the existing selectedFilters array to avoid mutating state directly
		const updatedSelectedFilters = [...selectedFilters];
	  
		// Find the index of the selected item in the selectedFilters array
		const index = updatedSelectedFilters.findIndex(filter => filter.key === key && filter.type === type);
	  
		if (index !== -1) {
		  // If the item is already selected, remove it from the array
		  updatedSelectedFilters.splice(index, 1);
		} else {
		  // If the item is not selected, add it to the array
		  updatedSelectedFilters.push({ key, type });
		}
	  
		// Update the state with the new selectedFilters array
		setSelectedFilters(updatedSelectedFilters);

		// Update the selected filters in the AppLayout component
		updateSelectedFilters(updatedSelectedFilters);
	};

	const clearFilters = (e) => {
		e.preventDefault();
		setSelectedFilters([]);

		// Update the selected filters in the AppLayout component
		updateSelectedFilters([]);
	};

	const { isLoading, error, data } = useQuery(['templates'], () => fetch(
        `${template_market_obj.rest_args.endpoint}/template/library`, 
            {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': template_market_obj.rest_args.nonce,
            }
        }).then(res => res.json() )
    );

	function getSidebarData(data) {
		console.log('Data: ', data)
		setTemplates(data.templates);
		setCategories(data.categories);
		setPlugins(data.plugins);
		setPluginGroups(data.plugins_groups);

		// Filter templates based on type
		const templatesOfType = data.templates.filter(template => template.type === templateType);

		// Count for categories based on templates of the specific type
		const categoryCount = {};
		templatesOfType.forEach(template => {
			template.categories.forEach(category => {
				if (categoryCount[category]) {
					categoryCount[category]++;
				} else {
					categoryCount[category] = 1;
				}
			});
		});
		setCountCategories(categoryCount);
	
		// Count for plugin maps based on templates of the specific type
		const pluginCount = {};
		Object.keys(data.plugins).forEach(plugin => {
			pluginCount[plugin] = templatesOfType.filter(template => template.required_plugins.some(reqPlugin => reqPlugin.slug === plugin)).length;
		});
		setCountPlugins(pluginCount);
	}

	// Group plugins by their group
	const groupedPlugins = {};
	Object.keys(plugins).forEach(plugin => {
		const group = plugins[plugin].group;
		if (!groupedPlugins[group]) {
			groupedPlugins[group] = [];
		}
		groupedPlugins[group].push(plugin);
	});

	const filterTemplates = (templateType) => {
		// Filter templates based on selectedFilters and templateType
		const newFilteredTemplates = templates.filter(template => {
			// Check if the template type matches the specified templateType
			if (template.type !== templateType) {
				return false;
			}
		
			return selectedFilters.some(filter => {
				if (filter.type === 'plugins') {
					// Check if any required plugin matches the selected plugin
					return template.required_plugins.some(requiredPlugin => requiredPlugin.slug === filter.key);
				} else if (filter.type === 'categories') {
					// Check if the template includes the selected category
					return template.categories.includes(filter.key);
				}
				return false;
			});
		});
	  
		// Update the state with the filtered templates
		setFilteredTemplates(newFilteredTemplates);
	};
	  

	useEffect(() => {
        if (data) {
           getSidebarData(data);
            
        } else {
            console.log('No Data')
        }

    }, [isLoading]);

	useEffect(() => {
        filterTemplates(templateType);

    }, [selectedFilters]);

	// console.log('Selected Filters: ', selectedFilters);
	// console.log('filteredTemplates: ', filteredTemplates);

	return (
		<SidebarStyle className="templatiq__sidebar">
			{
				isLoading ? <ContentLoading style={ { margin: 0, width: '256px' } } /> : 
				<>
					<div className="templatiq__sidebar__top">
						<h3 className="templatiq__sidebar__top__title">
							<ReactSVG src={ filterIcon } width={18} height={18} />
							Filter
						</h3>
						<button 
							href="#" 
							className="templatiq__sidebar__top__clear"
							onClick = {clearFilters}
							disabled={selectedFilters.length === 0}
						>
							Clear
						</button>
					</div>
					<div className="templatiq__sidebar__wrapper">
						<Tabs>
							<TabList className="templatiq__sidebar__nav">
								<Tab className="templatiq__sidebar__nav__item">
									<a href="#" className="templatiq__sidebar__nav__link">Plugins</a>
								</Tab>
								<Tab className="templatiq__sidebar__nav__item">
									<a href="#" className="templatiq__sidebar__nav__link">Categories</a>
								</Tab>
							</TabList>
							<TabPanel>
								<SidebarItemStyle className="templatiq__sidebar__filter templatiq__sidebar__plugins">
									<Accordion transition transitionTimeout={250} className="templatiq__sidebar__accordion">
										{Object.keys(groupedPlugins).map((group, index) => (
											<AccordionItem
												key={group}
												header={pluginGroups[group]}
												className="templatiq__sidebar__accordion__single"
												initialEntered={index === 0} // Set initialEntered to true for the first item
											>
												<div className="templatiq__sidebar__accordion__item">
												{groupedPlugins[group].map(pluginKey => (
													<div key={pluginKey} className="templatiq__sidebar__filter__single templatiq__checkbox">
														<input
															type="checkbox"
															id={pluginKey}
															className="templatiq__sidebar__filter__single__checkbox templatiq__checkbox__input"
															onChange={() => handleFilter(pluginKey, 'plugins')}
															checked={selectedFilters.some(filter => filter.key === pluginKey && filter.type === 'plugins')}
														/>
														<label
															htmlFor={pluginKey}
															className="templatiq__sidebar__filter__single__label templatiq__checkbox__label"
														>
															{data.plugins[pluginKey].name}
														</label>
														<span className="templatiq__sidebar__filter__single__count templatiq__checkbox__count">
															{countPlugins[pluginKey] || 0}
														</span>
													</div>
												))}
												</div>
											</AccordionItem>
										))}
									</Accordion>
								</SidebarItemStyle>
							</TabPanel>
							<TabPanel>
								<SidebarItemStyle className="templatiq__sidebar__filter templatiq__sidebar__categories">
									<div className="templatiq__sidebar__categories__wrapper">
										{Object.keys(categories).map((categoryKey) => (
											<div className="templatiq__sidebar__filter__single templatiq__checkbox">
												<input 
													type="checkbox" id={categoryKey} className="templatiq__sidebar__filter__single__checkbox templatiq__checkbox__input"
													onChange={() => handleFilter(categoryKey, 'categories')}
													checked={selectedFilters.some(filter => filter.key === categoryKey && filter.type === 'categories')}
												/>
												<label for={categoryKey} className="templatiq__sidebar__filter__single__label templatiq__checkbox__label">{categories[categoryKey]}</label>
												<span className="templatiq__sidebar__filter__single__count templatiq__checkbox__count">{countCategories[categoryKey] || 0}</span>
											</div>
										))}
									</div>
								</SidebarItemStyle>
							</TabPanel>
						</Tabs>
					</div>
				</>
			}
		</SidebarStyle>
	);
}

export default Sidebar;
