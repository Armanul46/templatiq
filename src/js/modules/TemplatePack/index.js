import ReactSVG from 'react-inlinesvg';
import { TemplatePackStyle, TemplatePackFilterStyle } from '@root/style';
import SingleTemplate from "@components/SingleTemplate";
import Searchform from "@components/Searchform";

import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import 'react-tabs/style/react-tabs.css';

import ReactPaginate from 'react-paginate';

import { useQuery } from '@tanstack/react-query';
import templatesData from '../../data/template.json';


import searchIcon from '@icon/search.svg';
import crownIcon from '@icon/crown.svg';
import arrowLeft from '@icon/angle-left.svg';
import arrowRight from '@icon/angle-right.svg';
import elementorIcon from "@icon/elementor.svg";
import directoristIcon from "@icon/directorist.svg";

import templateImg1 from "@images/template/1.svg";
import templateImg2 from "@images/template/2.svg";
import templateImg3 from "@images/template/3.svg";
import Popup from "@components/Popup";
import AppLayout from "@layout/AppLayout";

export default function TemplatePackModule() {

	let total = 6;
	let perPage = 4;

	let handlePageClick = ( data ) => {
		return data;
	}

	const { isLoading, error, data } = useQuery(['templates'], () => templatesData);

	if (isLoading) return 'Loading...';

	if (error) return 'An error has occurred: ' + error.message;


	return (
		<AppLayout>
			<TemplatePackStyle className="templatiq__content">
				<Popup />
				<Tabs className="templatiq__content__tab">
					<div className="templatiq__content__top">
						<div className="templatiq__content__top__filter">
							<h3 className="templatiq__content__top__filter__title">
								Template Pack
							</h3>
							<TemplatePackFilterStyle className="templatiq__content__top__filter__wrapper">
								<TabList className="templatiq__content__top__filter__tablist">
									<Tab className="templatiq__content__top__filter__item">
										<a href="#" className="templatiq__content__top__filter__link">All (24)</a>
									</Tab>
									<Tab className="templatiq__content__top__filter__item">
										<a href="#" className="templatiq__content__top__filter__link">Free (8)</a>
									</Tab>
									<Tab className="templatiq__content__top__filter__item">
										<a href="#" className="templatiq__content__top__filter__link">
											<ReactSVG src={crownIcon} width={12} height={12} />
											Pro (16)
										</a>
									</Tab>
								</TabList>
							</TemplatePackFilterStyle>
						</div>
						<div className="templatiq__content__top__search">
							<Searchform />
						</div>
					</div>

					<div className="templatiq__content__wrapper">
						<TabPanel className="templatiq__content__tab-panel">
							{data
							.map(template => (
								<SingleTemplate 
									// img = {template.img} 
									img = {templateImg1} 
									title = {template.title} 
									price = {template.price} 
									downloadCount = {template.downloadCount} 
									favoriteCount = {template.favoriteCount} 
									requiredPlugins = {template.requiredPlugins}
									categories = {template.categories}
								
								/>
							))}
						</TabPanel>
						<TabPanel className="templatiq__content__tab-panel">
						{data
							.filter(template => template.price === "")
							.map(template => (
								<SingleTemplate 
									// img = {template.img} 
									img = {templateImg1} 
									title = {template.title} 
									price = {template.price} 
									downloadCount = {template.downloadCount} 
									favoriteCount = {template.favoriteCount} 
									requiredPlugins = {template.requiredPlugins}
									categories = {template.categories}
								
								/>
							))}
						</TabPanel>
						<TabPanel className="templatiq__content__tab-panel">
						{data
							.filter(template => template.price !== "")
							.map(template => (
								<SingleTemplate 
									// img = {template.img} 
									img = {templateImg1} 
									title = {template.title} 
									price = {template.price} 
									downloadCount = {template.downloadCount} 
									favoriteCount = {template.favoriteCount} 
									requiredPlugins = {template.requiredPlugins}
									categories = {template.categories}
								
								/>
							))}
						</TabPanel>

						{ total > perPage && (
							<ReactPaginate
								breakLabel="..."
								onPageChange={ handlePageClick }
								nextLabel={ <ReactSVG src={ arrowRight } /> }
								previousLabel={ <ReactSVG src={ arrowLeft } /> }
								pageRangeDisplayed={ 3 }
								pageCount={ Math.ceil( total / perPage ) }
								previousClassName="templatiq-pagination__item"
								previousLinkClassName="templatiq-pagination__link templatiq-pagination__control"
								nextClassName="templatiq-pagination__item"
								nextLinkClassName="templatiq-pagination__link templatiq-pagination__control"
								containerClassName="templatiq-pagination"
								pageClassName="templatiq-pagination__item"
								pageLinkClassName="templatiq-pagination__link"
								activeLinkClassName="templatiq-pagination__active"
								renderOnZeroPageCount={ null }
							/>
						) }
					</div>
				</Tabs>
			</TemplatePackStyle>
		</AppLayout>
	);
}
