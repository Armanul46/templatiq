import Styled from 'styled-components';

const TemplatePackStyle = Styled.div`
    .templatiq__content__tab {
        display: flex;
        gap: 24px;
        flex-direction: column;
    }
    
    .templatiq__content__top {
        display: flex;
        gap: 24px;
        justify-content: space-between;
        .templatiq__content__top__filter__title {
            font-size: 16px;
            font-weight: 600;
            color: #101014;
            margin: 0;
        }
        .templatiq__content__top__filter {
            display: flex;
            gap: 20px;
            align-items: center;
        }
    }

    .templatiq__content__tab-panel {
        display: flex;
        flex-wrap: wrap;
        gap: 32px;
    }

    .templatiq-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-top: 32px;
        li {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #ffffff;
            border: 1px solid #E8E8EE;
            border-right: none;
            cursor: pointer;
            a {
                color: #57575F;
                box-shadow: none;
            }
            &:first-child {
                border-radius: 8px 0 0 8px;
            }
            &:last-child {
                border-radius: 0 8px 8px 0;
                border-right: 1px solid #E8E8EE;
            }
            &.selected,
            &:hover {
                background: #F1F1F4;
            }
            &.break,
            &.disabled {
                pointer-events: none;
            }
        }
    }
`;

const TemplatePackFilterStyle = Styled.div`
    .templatiq__content__top__filter__tablist {
        display: flex;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 8px;
        background-color: #ffffff;
        box-shadow: 0 4px 16px #10101404;
    }
    .templatiq__content__top__filter__item {
        display: flex;
        &.react-tabs__tab--selected {
            a {
                color: #ffffff;
                background: #8941FF;
            }
        }
    }
    .templatiq__content__top__filter__link {
        display: flex;
        gap: 8px;
        align-items: center;
        padding: 7px 12px;
        border-radius: 8px;
        color: #57575F;
        font-size: 12px;
        line-height: 1;
        text-transform: uppercase;
        background: transparent;
        transition: background 0.3s ease, color 0.3s ease;
        &:hover {
            color: #ffffff;
            background: #8941FF;
        }
    }

`;


export { TemplatePackStyle, TemplatePackFilterStyle };