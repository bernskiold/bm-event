/**
 * Preview of a session in the editor.
 *
 * @param {Object} props
 * @param {Object} props.data Session from the REST API.
 */
export default function SessionListItem( props ) {
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
		<div className="session-list-item" id={ `session-${ id.toString() }` }>
			<div className="session-list-item-time">
				{ startTime && (
					<time className="session-list-item-time-start">
						{ startTime }
					</time>
				) }

				{ endTime && (
					<>
						<span className="session-list-item-time-separator"></span>
						<time className="session-list-item-time-end">
							{ endTime }
						</time>
					</>
				) }
			</div>
			<div className="session-list-item-info">
				<h3 className="session-list-item-title">{ title.rendered }</h3>

				{ topic && (
					<p className="session-list-item-topic">{ topic }</p>
				) }

				{ shortDescription && (
					<p className="session-list-item-short-description">
						{ shortDescription }
					</p>
				) }

				{ speakers && (
					<div className="session-list-item-speakers">
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
