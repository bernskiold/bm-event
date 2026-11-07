/**
 * Preview of a session in the editor.
 *
 * @param {Object} props
 * @param {Object} props.data Session from the REST API.
 */
export default function SessionGridItem( props ) {
	const { data } = props;
	const {
		id,
		title,
		start_time: startTime,
		end_time: endTime,
		topic,
		short_description: shortDescription,
		speakers,
	} = data;
	return (
		<div className="session-grid-item" id={ `session-${ id.toString() }` }>
			<div className="session-grid-time">
				{ startTime && (
					<time className="session-grid-time-start">
						{ startTime }
					</time>
				) }

				{ endTime && (
					<>
						<span className="session-grid-time-separator"></span>
						<time className="session-grid-time-end">
							{ endTime }
						</time>
					</>
				) }
			</div>
			<div className="session-grid-info">
				<h3 className="session-grid-title">{ title.rendered }</h3>

				{ topic && <p className="session-grid-topic">{ topic }</p> }

				{ shortDescription && (
					<p className="session-grid-short-description">
						{ shortDescription }
					</p>
				) }

				{ speakers && (
					<div className="session-grid-speakers">
						{ speakers.map( ( speaker, index ) => (
							<div className="speaker" key={ index }>
								<p className="speaker-name">{ speaker.name }</p>

								{ speaker.title && (
									<p className="speaker-title">
										{ speaker.title }
									</p>
								) }
							</div>
						) ) }
					</div>
				) }
			</div>
		</div>
	);
}
