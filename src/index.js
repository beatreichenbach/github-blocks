import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import {
    TextControl,
    PanelBody,
    CheckboxControl
} from '@wordpress/components';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';

registerBlockType( 'github/list-repos', {
    apiVersion: 2,
    title: 'GitHub: Repositories',
    icon: 'admin-links',
    category: 'embed',
    attributes: {
        username: {
            type: 'string',
            default: ''
        },
        profileButton: {
            type: 'boolean',
            default: false
        },
    },
    edit: function ( props ) {
        const blockProps = useBlockProps();

        function onChangeProfileButton( checked ) {
            props.setAttributes( {profileButton: checked });
        }

        function onChangeUsername( username ) {
            props.setAttributes( { username: username } );
        }

        return (
            <div { ...blockProps }>
                <InspectorControls key="setting">
                    <PanelBody title={__('Settings')}>
                        <TextControl
                            label="Username"
                            help="https://www.github.com/<username>"
                            value={ props.attributes.username }
                            onChange={ onChangeUsername }
                        />
                        <CheckboxControl
                            label="Profile Button"
                            help="Adds a button that links to the GitHub profile."
                            checked={ props.attributes.profileButton }
                            onChange={ onChangeProfileButton }
                        />
                    </PanelBody>
                </InspectorControls>
                <ServerSideRender
                    block="github/list-repos"
                    attributes={ props.attributes }
                />
            </div>
        );
    },
    save: function ( props ) {
        return null;
    },
} );
