import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { PanelBody, PanelRow, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const ContentDecayPanel = () => {
    const [ decayData, setDecayData ] = useState( null );
    const [ loading, setLoading ] = useState( true );

    const postId = useSelect( ( select ) => {
        return select( 'core/editor' ).getCurrentPostId();
    } );

    useEffect( () => {
        if ( ! postId ) {
            return;
        }

        setLoading( true );

        fetch( `/wp-json/content-decay/v1/reports?limit=100`, {
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce,
            },
        } )
            .then( ( r ) => r.json() )
            .then( ( data ) => {
                const postData = data.find( ( item ) => item.post_id === postId );
                setDecayData( postData || null );
                setLoading( false );
            } )
            .catch( () => {
                setLoading( false );
            } );
    }, [ postId ] );

    const getScoreColor = ( score ) => {
        if ( score >= 70 ) return '#46b450';
        if ( score >= 40 ) return '#ffb900';
        return '#dc3232';
    };

    return (
        <>
            <PluginSidebarMoreMenuItem target="cdd-sidebar">
                { __( 'Content Decay', 'content-decay-detector' ) }
            </PluginSidebarMoreMenuItem>
            <PluginSidebar
                name="cdd-sidebar"
                title={ __( 'Content Decay', 'content-decay-detector' ) }
            >
                <PanelBody>
                    { loading && (
                        <PanelRow>
                            <Spinner />
                            <span style={ { marginLeft: '8px' } }>
                                { __( 'Loading decay data...', 'content-decay-detector' ) }
                            </span>
                        </PanelRow>
                    ) }

                    { ! loading && ! decayData && (
                        <PanelRow>
                            <p style={ { color: '#46b450', fontWeight: 'bold' } }>
                                { __( '✓ No decay detected for this post.', 'content-decay-detector' ) }
                            </p>
                        </PanelRow>
                    ) }

                    { ! loading && decayData && (
                        <>
                            <PanelRow>
                                <strong>{ __( 'Decay Score:', 'content-decay-detector' ) }</strong>
                                <span
                                    style={ {
                                        background: getScoreColor( decayData.decay_score ),
                                        color: '#fff',
                                        padding: '2px 10px',
                                        borderRadius: '3px',
                                        fontWeight: 'bold',
                                        marginLeft: '8px',
                                    } }
                                >
                                    { decayData.decay_score }
                                </span>
                            </PanelRow>
                            <PanelRow>
                                <strong>{ __( 'Last Scanned:', 'content-decay-detector' ) }</strong>
                                <span style={ { marginLeft: '8px' } }>{ decayData.snapshot_date }</span>
                            </PanelRow>
                            { decayData.suggestions && decayData.suggestions.length > 0 && (
                                <PanelRow>
                                    <div>
                                        <strong>{ __( 'Suggestions:', 'content-decay-detector' ) }</strong>
                                        <ul style={ { marginTop: '8px', paddingLeft: '16px' } }>
                                            { decayData.suggestions.map( ( suggestion, index ) => (
                                                <li key={ index }>{ suggestion }</li>
                                            ) ) }
                                        </ul>
                                    </div>
                                </PanelRow>
                            ) }
                        </>
                    ) }
                </PanelBody>
            </PluginSidebar>
        </>
    );
};

registerPlugin( 'content-decay-detector', {
    render: ContentDecayPanel,
} );